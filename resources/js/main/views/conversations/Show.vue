<template>
    <a-layout>
        <LeftSidebar />
        <a-layout :style="{ marginLeft: '240px' }">
            <a-layout-content class="main-content">
                <div class="chat-container">
                    <!-- Leads Panel -->
                    <div class="leads-panel">
                        <div class="search-container p-3">
                            <a-input
                                v-model:value="searchQuery"
                                placeholder="Search leads..."
                                @input="onLeadSearch"
                            >
                                <template #prefix>
                                    <SearchOutlined />
                                </template>
                            </a-input>
                        </div>
                        
                        <div class="leads-list">
                            <a-list
                                :data-source="filteredLeads"
                                :loading="loading"
                            >
                                <template #renderItem="{ item }">
                                    <a-list-item
                                        class="lead-item"
                                        :class="{ active: selectedLead && selectedLead.id === item.id }"
                                        @click="selectLead(item)"
                                    >
                                        <div class="lead-item-content">
                                            <div class="lead-avatar">
                                                {{ getInitials(item) }}
                                            </div>
                                            <div class="lead-details">
                                                <div class="lead-name">
                                                    {{ item.dynamic_fields['First Name'] || 'Unknown' }}
                                                    {{ item.dynamic_fields['Last Name'] || '' }}
                                                </div>
                                                <div class="lead-phone">
                                                    {{ getPhoneNumber(item) }}
                                                </div>
                                            </div>
                                        </div>
                                    </a-list-item>
                                </template>
                            </a-list>
                        </div>
                    </div>

                    <!-- Chat Panel -->
                    <div class="chat-panel">
                        <template v-if="selectedLead">
                            <!-- Chat Header -->
                            <div class="chat-header">
                                <div class="lead-avatar">
                                    {{ getInitials(selectedLead) }}
                                </div>
                                <div class="lead-info">
                                    <div class="lead-name">
                                        {{ selectedLead.dynamic_fields['First Name'] || 'Unknown' }}
                                        {{ selectedLead.dynamic_fields['Last Name'] || '' }}
                                    </div>
                                    <div class="lead-status">
                                        {{ getPhoneNumber(selectedLead) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Messages Container -->
                            <div class="messages-container">
                                <div 
                                    v-for="(message, index) in messages" 
                                    :key="index" 
                                    class="message-wrapper"
                                    :class="{
                                        'outgoing': message.sender === 'system',
                                        'incoming': message.sender !== 'system'
                                    }"
                                >
                                    <div class="message">
                                        <div class="message-content">{{ message.text }}</div>
                                        <div class="message-time">
                                            {{ formatMessageTime(message.timestamp) }}
                                        </div>
                                        <div v-if="message.sender === 'system'" class="message-status">
                                            {{ message.status }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Input -->
                            <div class="message-input-container">
                                <a-input
                                    v-model:value="newMessage"
                                    placeholder="Type a message..."
                                    class="message-input"
                                    @keyup.enter="sendMessage"
                                >
                                    <template #prefix>
                                        <i class="ri-message-2-line" style="color: #9ca3af"></i>
                                    </template>
                                </a-input>
                                <button 
                                    class="send-button"
                                    :disabled="!newMessage.trim()"
                                    @click="sendMessage"
                                >
                                    <i class="ri-send-plane-fill"></i>
                                    Send
                                </button>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div v-else class="empty-state">
                            <i class="ri-message-2-line"></i>
                            <h3>Select a lead to start chatting</h3>
                            <p>Choose a lead from the list to view and send messages</p>
                        </div>
                    </div>
                </div>
            </a-layout-content>
        </a-layout>
    </a-layout>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import { SearchOutlined } from '@ant-design/icons-vue';
import LeftSidebar from "../../../common/layouts/LeftSidebar.vue";
import common from "../../../common/composable/common";

export default {
    components: {
        LeftSidebar,
        SearchOutlined
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const { formatDateTime } = common();
        
        const campaignId = route.params.id;
        const campaignName = ref('');
        const leads = ref([]);
        const filteredLeads = ref([]);
        const searchQuery = ref('');
        const loading = ref(true);
        const selectedLead = ref(null);
        const newMessage = ref('');
        const messages = ref([]);

        const leadsColumns = ref([]);

        const getStatusColor = (status) => {
            const statusColors = {
                'New': 'blue',
                'Contacted': 'green',
                'Qualified': 'purple',
                'Unqualified': 'red'
            };
            return statusColors[status] || 'default';
        };

        const selectLead = (lead) => {
            console.log('Selected lead:', {
                id: lead.id,
                xid: lead.xid,
                phone: getPhoneNumber(lead)
            });
            selectedLead.value = {
                ...lead,
                name: `${lead.dynamic_fields['First Name'] || ''} ${lead.dynamic_fields['Last Name'] || ''}`.trim() || 'Unknown',
            };
            fetchMessages(lead.id);
        };

        const onLeadSearch = (event) => {
            const query = event.target.value.toLowerCase();
            searchQuery.value = query;
            
            if (!query) {
                filteredLeads.value = leads.value;
                return;
            }

            filteredLeads.value = leads.value.filter(lead => {
                const phoneNumber = getPhoneNumber(lead).toLowerCase();
                const name = `${lead.dynamic_fields['First Name'] || ''} ${lead.dynamic_fields['Last Name'] || ''}`.toLowerCase();
                
                return phoneNumber.includes(query) || 
                       name.includes(query) ||
                       (lead.dynamic_fields['First Name'] || '').toLowerCase().includes(query) ||
                       (lead.dynamic_fields['Last Name'] || '').toLowerCase().includes(query);
            });
            
            console.log('Search query:', query);
            console.log('Filtered leads:', filteredLeads.value.length);
        };

        const sendMessage = async () => {
            if (!selectedLead.value || !newMessage.value.trim()) return;
            
            const messageText = newMessage.value.trim();
            console.log('Sending message to lead:', {
                id: selectedLead.value.id,
                xid: selectedLead.value.xid,
                message: messageText
            });

            // Optimistically add message to UI
            messages.value.push({
                id: 'temp-' + Date.now(),
                sender: 'system',
                text: messageText,
                timestamp: new Date().toISOString(),
                status: 'sending'
            });

            newMessage.value = '';

            try {
                const response = await axiosAdmin.post(
                    `leads/${selectedLead.value.id}/conversations`,
                    { message: messageText }
                );
                console.log('Send response:', response.data); // Debug log
                
                // Replace temporary message with actual one from server
                const conversation = response.data.response_data.conversation;
                const lastIndex = messages.value.length - 1;
                messages.value[lastIndex] = {
                    id: conversation.id,
                    sender: conversation.direction === 'inbound' ? 'user' : 'system',
                    text: conversation.message,
                    timestamp: conversation.created_at,
                    status: conversation.status,
                    delivered_at: conversation.delivered_at,
                    read_at: conversation.read_at
                };

                // Refresh messages to ensure everything is in sync
                await fetchMessages(selectedLead.value.id);
            } catch (error) {
                console.error('Error sending message:', error);
                message.error('Failed to send message');
                
                // Remove the temporary message on error
                messages.value = messages.value.filter(msg => msg.id !== 'temp-' + Date.now());
            }
        };

        const formatMessageTime = (timestamp) => {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            const now = new Date('2024-12-15T16:05:14+05:30');
            
            const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
            
            if (diffInHours < 24) {
                return date.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
            } else if (diffInHours < 168) { // within a week
                return date.toLocaleDateString('en-US', { weekday: 'short' });
            } else {
                return date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric' 
                });
            }
        };

        const getPhoneNumber = (lead) => {
            // Check all possible variations of phone number field
            return lead['Phone Number'] || 
                   lead['Phone No'] || 
                   lead['phone number'] || 
                   lead['phone no'] ||
                   lead['Phone no'] ||
                   lead.phone_number ||
                   lead.phone_no ||
                   'No Phone Number';
        };

        const getInitials = (lead) => {
            const firstName = lead.dynamic_fields['First Name'] || '';
            const lastName = lead.dynamic_fields['Last Name'] || '';
            return (firstName.charAt(0) + lastName.charAt(0)).toUpperCase() || '?';
        };

        const fetchLeads = async () => {
            try {
                loading.value = true;
                
                // Use custom endpoint to fetch campaign leads
                const leadsResponse = await axiosAdmin.get(`leads/campaign/${campaignId}`);
                
                // Extract campaign and leads from response
                const { campaign, leads: fetchedLeads } = leadsResponse.data;
                
                // Safely set campaign name with fallback
                campaignName.value = campaign?.name || 'Unknown Campaign';
                
                // Process leads with dynamic fields
                leads.value = fetchedLeads.map(lead => {
                    console.log('Lead details:', {
                        id: lead.id,
                        xid: lead.xid,
                        phone: lead.dynamic_fields['Phone No'] || lead.dynamic_fields['phone_number']
                    });
                    
                    // Combine dynamic fields with lead base data
                    const processedLead = {
                        ...lead,
                        ...lead.dynamic_fields,
                        name: `${lead.dynamic_fields['First Name'] || ''} ${lead.dynamic_fields['Last Name'] || ''}`.trim() || 'Unknown',
                        profile_picture: lead.profile_picture || `https://ui-avatars.com/api/?name=${encodeURIComponent(lead.dynamic_fields['First Name'] || 'U')}`,
                        online: Math.random() < 0.5,
                        unread_count: Math.floor(Math.random() * 5),
                        last_message: {
                            text: 'Click to start conversation',
                            timestamp: new Date('2024-12-15T16:21:16+05:30').toISOString()
                        }
                    };
                    
                    return processedLead;
                });

                // Initialize filtered leads with all leads
                filteredLeads.value = leads.value;
                
                loading.value = false;
            } catch (error) {
                console.error('Error Object:', error);
                
                if (error.response) {
                    console.error('Error Response:', error.response.data);
                    message.error(error.response.data.message || 'Failed to fetch leads');
                } else if (error.request) {
                    console.error('Error Request:', error.request);
                    message.error('No response received from server');
                } else {
                    console.error('Error:', error.message);
                    message.error('Error setting up the request');
                }
                
                loading.value = false;
            }
        };

        const fetchMessages = async (leadId) => {
            try {
                loading.value = true;
                const response = await axiosAdmin.get(`leads/${leadId}/conversations`);
                console.log('Response:', response.data); // Debug log
                
                // Get conversations from response data
                const { lead, conversations } = response.data;
                
                messages.value = conversations.map(conv => ({
                    id: conv.id,
                    sender: conv.direction === 'inbound' ? 'user' : 'system',
                    text: conv.message,
                    timestamp: conv.created_at,
                    status: conv.status,
                    delivered_at: conv.delivered_at,
                    read_at: conv.read_at
                }));
                
                loading.value = false;
            } catch (error) {
                console.error('Error fetching messages:', error);
                message.error('Failed to fetch messages');
                loading.value = false;
            }
        };

        onMounted(() => {
            fetchLeads();
        });

        return {
            leads,
            filteredLeads,
            searchQuery,
            loading,
            leadsColumns,
            selectedLead,
            messages,
            newMessage,
            formatDateTime,
            formatMessageTime,
            selectLead,
            onLeadSearch,
            sendMessage,
            getPhoneNumber,
            getInitials
        };
    }
}
</script>

<style scoped>
.chat-container {
    display: flex;
    height: calc(100vh - 64px); /* Exact height minus header */
    margin: 0;
    background: #fff;
}

:deep(.ant-layout) {
    background: #f0f2f5 !important;
}

:deep(.ant-layout-has-sider) {
    height: 100vh;
    overflow: hidden;
}

:deep(.ant-layout-content) {
    padding: 0 !important;
    margin: 0 !important;
}

.main-content {
    height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.leads-panel {
    width: 350px;
    background: #fff;
    border-right: 1px solid #e1e4e8;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.leads-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px;
    height: calc(100% - 80px);
}

.chat-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #fff;
    height: 100%;
}

.messages-container {
    flex: 1;
    height: calc(100% - 144px);
    overflow-y: auto;
    padding: 24px;
    background: #f0f2f5;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.search-container {
    padding: 16px;
    border-bottom: 1px solid #e1e4e8;
    background: #fff;
}

.search-container :deep(.ant-input-affix-wrapper) {
    border-radius: 8px;
    padding: 8px 12px;
    border: 1px solid #e1e4e8;
    transition: all 0.3s;
}

.search-container :deep(.ant-input-affix-wrapper:hover) {
    border-color: #1890ff;
}

.search-container :deep(.ant-input-affix-wrapper-focused) {
    box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.lead-item {
    padding: 12px !important;
    border-radius: 8px !important;
    margin-bottom: 4px !important;
    transition: all 0.3s ease;
    border: none !important;
}

.lead-item:hover {
    background: #f5f7f9;
}

.lead-item.active {
    background: #e6f7ff;
}

.lead-item-content {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
}

.lead-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #1890ff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 500;
    flex-shrink: 0;
}

.lead-details {
    flex: 1;
    min-width: 0;
}

.lead-name {
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.lead-phone {
    font-size: 13px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-header {
    padding: 16px 24px;
    background: #fff;
    border-bottom: 1px solid #e1e4e8;
    display: flex;
    align-items: center;
    gap: 16px;
    height: 72px;
}

.chat-header .lead-avatar {
    width: 40px;
    height: 40px;
    background: #1890ff;
}

.chat-header .lead-info {
    flex: 1;
}

.chat-header .lead-name {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 2px;
}

.chat-header .lead-status {
    font-size: 13px;
    color: #6b7280;
}

.message-wrapper {
    display: flex;
    flex-direction: column;
    max-width: 70%;
}

.message-wrapper.incoming {
    align-self: flex-start;
}

.message-wrapper.outgoing {
    align-self: flex-end;
}

.message {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.message-content {
    padding: 12px 16px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.5;
    position: relative;
    word-break: break-word;
}

.incoming .message-content {
    background: #fff;
    color: #1f2937;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.outgoing .message-content {
    background: #1890ff;
    color: #fff;
    border-bottom-right-radius: 4px;
}

.message-time {
    font-size: 11px;
    margin-top: 2px;
    opacity: 0.8;
}

.incoming .message-time {
    color: #6b7280;
    margin-left: 4px;
}

.outgoing .message-time {
    color: #fff;
    text-align: right;
    margin-right: 4px;
}

.message-status {
    font-size: 11px;
    text-align: right;
    margin-top: -2px;
}

.outgoing .message-status {
    color: rgba(255, 255, 255, 0.8);
}

.message-input-container {
    padding: 16px 24px;
    background: #fff;
    border-top: 1px solid #e1e4e8;
    display: flex;
    align-items: center;
    gap: 12px;
}

.message-input {
    flex: 1;
}

.message-input :deep(.ant-input) {
    padding: 10px 16px;
    border-radius: 24px;
    border: 1px solid #e1e4e8;
    transition: all 0.3s;
    font-size: 14px;
}

.message-input :deep(.ant-input:hover) {
    border-color: #1890ff;
}

.message-input :deep(.ant-input:focus) {
    border-color: #1890ff;
    box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.send-button {
    height: 40px;
    padding: 0 24px;
    border-radius: 20px;
    background: #1890ff;
    color: white;
    border: none;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.send-button:hover {
    background: #096dd9;
}

.send-button:disabled {
    background: #e5e7eb;
    cursor: not-allowed;
    color: #9ca3af;
}

.empty-state {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: #6b7280;
    width: 100%;
    max-width: 400px;
    padding: 0 24px;
}

.empty-state i {
    font-size: 48px;
    color: #d1d5db;
    margin-bottom: 16px;
}

.empty-state h3 {
    font-size: 18px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.empty-state p {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}
</style>
