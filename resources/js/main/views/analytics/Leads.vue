<template>
    <a-layout>
        <LeftSidebar />
        <a-layout style="margin-left: 240px">
            <AdminPageHeader>
                <template #header>
                    <a-page-header 
                        :title="$t('menu.leads')" 
                        @back="$router.push({ name: 'admin.analyze.index' })" 
                        class="p-0" 
                    />
                </template>
                <template #breadcrumb>
                    <a-breadcrumb separator="-" style="font-size: 12px">
                        <a-breadcrumb-item>
                            <router-link :to="{ name: 'admin.dashboard.index' }">
                                {{ $t(`menu.dashboard`) }}
                            </router-link>
                        </a-breadcrumb-item>
                        <a-breadcrumb-item>
                            <router-link :to="{ name: 'admin.analyze.index' }">
                                {{ $t(`menu.analytics`) }}
                            </router-link>
                        </a-breadcrumb-item>
                        <a-breadcrumb-item>
                            {{ $t(`menu.leads`) }}
                        </a-breadcrumb-item>
                    </a-breadcrumb>
                </template>
            </AdminPageHeader>

            <div class="dashboard-page-content-container">
                <a-row :gutter="[15, 15]" class="mt-30 mb-20">
                    <a-col :span="24">
                        <a-card :title="campaignName" extra-class="leads-card">
                            <a-tabs v-model:activeKey="activeTab">
                                <a-tab-pane key="all" :tab="$t('leads.all_leads')">
                                    <a-table 
                                        :columns="allLeadsColumns" 
                                        :data-source="leads" 
                                        :loading="loading"
                                        rowKey="xid"
                                        :pagination="{
                                            total: leads.length,
                                            showSizeChanger: true,
                                            showQuickJumper: true,
                                            pageSizeOptions: ['10', '20', '50', '100']
                                        }"
                                    >
                                        <template #bodyCell="{ column, record }">
                                            <template v-if="column.key === 'status'">
                                                <a-tag :color="getStatusColor(record.status)">
                                                    {{ record.status }}
                                                </a-tag>
                                            </template>
                                            <template v-if="column.key === 'conversation_score'">
                                                <a-tag :color="getScoreColor(record.conversation_score)">
                                                    {{ record.conversation_score || 'N/A' }}
                                                </a-tag>
                                            </template>
                                            <template v-if="column.key === 'actions'">
                                                <a-space>
                                                    <a-button 
                                                        type="primary" 
                                                        size="small" 
                                                        @click="viewConversationAnalysis(record)"
                                                     >
                                                         {{ $t('leads.conversation_analysis') }}
                                                    </a-button>
                                                </a-space>
                                            </template>
                                        </template>
                                    </a-table>
                                </a-tab-pane>
                                <a-tab-pane key="status" :tab="$t('leads.leads_by_status')">
                                    <a-row :gutter="[16, 16]">
                                        <a-col 
                                            v-for="(statusGroup, status) in leadsByStatus" 
                                            :key="status" 
                                            :xs="24" 
                                            :sm="12" 
                                            :md="8" 
                                            :lg="6"
                                        >
                                            <a-card 
                                                :title="status" 
                                                :headStyle="{ backgroundColor: getStatusColor(status) }"
                                            >
                                                <div class="status-card-content">
                                                    <div class="status-count">
                                                        {{ statusGroup.length }}
                                                    </div>
                                                    <a-button 
                                                        type="link" 
                                                        @click="filterByStatus(status)"
                                                    >
                                                        {{ $t('leads.view_leads') }}
                                                    </a-button>
                                                </div>
                                            </a-card>
                                        </a-col>
                                    </a-row>
                                </a-tab-pane>
                            </a-tabs>
                        </a-card>
                    </a-col>
                </a-row>
            </div>

            <!-- Conversation Analysis Modal -->
            <a-modal 
                v-model:visible="analysisModalVisible" 
                :title="$t('leads.conversation_analysis')" 
                width="800px"
                :footer="null"
            >
                <a-table 
                    v-if="conversationAnalysis"
                    :columns="analyticsColumns"
                    :data-source="[conversationAnalysis]"
                    :pagination="false"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'conversation_score'">
                            <a-tag :color="getScoreColor(record.conversation_score)">
                                {{ record.conversation_score }}
                            </a-tag>
                        </template>
                    </template>
                </a-table>
                <a-empty v-else description="No conversation analysis available" />
            </a-modal>


        </a-layout>
    </a-layout>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import axios from 'axios';
import LeftSidebar from "../../../common/layouts/LeftSidebar.vue";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import common from "../../../common/composable/common";

export default {
    components: {
        LeftSidebar,
        AdminPageHeader
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const { formatDateTime } = common();
        
        const campaignId = route.params.id;
        const campaignName = ref('');
        const leads = ref([]);
        const loading = ref(true);
        const activeTab = ref('all');
        const analysisModalVisible = ref(false);
        const conversationAnalysis = ref(null);
        const baseColumns = [
            {
                title: 'Score',
                dataIndex: 'conversation_score',
                key: 'conversation_score',
                width: 80
            }
        ];
        const allLeadsColumns = ref([
            ...baseColumns,
            {
                title: 'Actions',
                key: 'actions',
                width: 150,
            }
        ]);
        const analyticsColumns = ref([
            {
                title: 'Conversation Score',
                dataIndex: 'conversation_score',
                key: 'conversation_score',
                width: 150,
                align: 'center',
            },
            {
                title: 'Conversation Analysis',
                dataIndex: 'analysis',
                key: 'analysis',
                width: 300,
                align: 'center',
            }
        ]);

        const leadsByStatus = computed(() => {
            return leads.value.reduce((acc, lead) => {
                if (!acc[lead.status]) {
                    acc[lead.status] = [];
                }
                acc[lead.status].push(lead);
                return acc;
            }, {});
        });

        const fetchLeads = async () => {
            try {
                loading.value = true;
                
                console.log('Fetching leads for Campaign XID:', campaignId);
                
                // Use custom endpoint to fetch campaign leads
                const leadsResponse = await axiosAdmin.get(`leads/campaign/${campaignId}`);
                
                console.log('Full Leads Response:', leadsResponse.data);
                
                // Extract campaign and leads from response
                const { campaign, leads: fetchedLeads } = leadsResponse.data;
                
                // Simultaneously fetch lead scores for all leads
                const leadScoresPromises = fetchedLeads.map(lead => 
                    axios.get(`/api/v1/leads/${lead.id}/conversation-analysis`)
                        .then(response => ({
                            leadId: lead.id, 
                            score: response.data.conversation_score || null,
                            analysis: response.data.analysis || null
                        }))
                        .catch(error => {
                            console.error(`Failed to fetch score for lead ${lead.id}:`, error);
                            return { 
                                leadId: lead.id, 
                                score: null, 
                                analysis: null 
                            };
                        })
                );

                // Wait for all lead score requests to complete
                const leadScores = await Promise.all(leadScoresPromises);

                // Create a map of lead scores for quick lookup
                const leadScoreMap = Object.fromEntries(
                    leadScores.map(ls => [ls.leadId, { 
                        conversation_score: ls.score, 
                        conversation_analysis: ls.analysis 
                    }])
                );
                
                // Safely set campaign name with fallback
                campaignName.value = campaign?.name || 'Unknown Campaign';
                
                // Process leads with dynamic fields and attach lead scores
                leads.value = fetchedLeads.map(lead => {
                    console.log('Processing Lead:', lead);
                    
                    // Get lead score from the map, default to null if not found
                    const leadScore = leadScoreMap[lead.id] || { 
                        conversation_score: null, 
                        conversation_analysis: null 
                    };
                    
                    // Combine dynamic fields with lead base data and lead score
                    const processedLead = {
                        ...lead,
                        ...lead.dynamic_fields,
                        ...leadScore,
                        status: lead.status || 'unknown',
                        first_actioner_name: lead.first_actioner?.name || 'N/A',
                        last_actioner_name: lead.last_actioner?.name || 'N/A'
                    };
                    
                    console.log('Processed Lead:', processedLead);
                    return processedLead;
                });
                
                // Update columns to include dynamic fields
                const dynamicColumnKeys = Object.keys(
                    leads.value.length > 0 ? leads.value[0].dynamic_fields : {}
                );
                
                const dynamicColumns = dynamicColumnKeys.map(key => ({
                    title: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                    dataIndex: key,
                    key: key
                }));
                
                // Update columns with dynamic fields
                allLeadsColumns.value = [
                    ...baseColumns,
                    ...dynamicColumns,
                    
                    {
                        title: 'Actions',
                        key: 'actions',
                        width: 150,
                    }
                ];
                
                console.log('Final Processed Leads:', leads.value);
                
                loading.value = false;
            } catch (error) {
                console.error('Complete Error Object:', error);
                
                // Detailed error handling
                if (error.response) {
                    // Server responded with an error
                    console.error('Error Response:', error.response);
                    message.error(
                        error.response.data.message || 
                        'Failed to fetch leads. Please try again.'
                    );
                } else if (error.request) {
                    // Request made but no response received
                    message.error('No response received from server. Please check your connection.');
                } else {
                    // Error before request was sent
                    message.error(`Error: ${error.message}`);
                }
                
                loading.value = false;
            }
        };

        const getStatusColor = (status) => {
            const statusColors = {
                
                'new': 'blue',
                'in_progress': 'orange',
                'converted': 'green',
                'lost': 'red'
            };
            return statusColors[status] || 'default';
        };

        const getScoreColor = (score) => {
            if (score >= 80) return 'green';
            if (score >= 50) return 'orange';
            return 'red';
        };

        const viewConversationAnalysis = async (record) => {
            try {
                console.log('Requesting analysis for lead:', record);
                analysisModalVisible.value = true;
                
                const response = await axios.get(`/api/v1/leads/${record.id}/conversation-analysis`);
                console.log('Analysis response:', response.data);
                
                conversationAnalysis.value = response.data;
                
                // Find and update the lead in the leads array
                const leadIndex = leads.value.findIndex(lead => lead.id === record.id);
                if (leadIndex !== -1) {
                    // Update the conversation score and analysis for the specific lead
                    leads.value[leadIndex] = {
                        ...leads.value[leadIndex],
                        conversation_score: response.data.conversation_score || 0,
                        conversation_analysis: response.data.analysis || null
                    };
                }
                
                if (response.data.success) {
                    message.success('Analysis completed successfully');
                } else {
                    message.error('Failed to analyze conversation');
                }
            } catch (error) {
                console.error('Analysis error:', error.response?.data || error.message);
                message.error('Failed to fetch conversation analysis');
            }
        };

        const filterByStatus = (status) => {
            activeTab.value = 'all';
            // Implement filtering logic here
            leads.value = leads.value.filter(lead => lead.status === status);
        };

        onMounted(() => {
            fetchLeads();
        });

        return {
            campaignName,
            leads,
            loading,
            activeTab,
            allLeadsColumns,
            leadsByStatus,
            formatDateTime,
            getStatusColor,
            filterByStatus,
            analysisModalVisible,
            conversationAnalysis,
            viewConversationAnalysis,
            getScoreColor,
            analyticsColumns
        };
    }
}
</script>

<style scoped>
.leads-card .ant-card-head {
    background-color: #f0f2f5;
}

.status-card-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-count {
    font-size: 24px;
    font-weight: bold;
    color: #1890ff;
}

.dashboard-page-content-container {
    padding: 20px;
}
</style>
