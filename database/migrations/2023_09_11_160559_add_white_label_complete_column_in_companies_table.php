<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddWhiteLabelCompleteColumnInCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('white_label_completed')->default(false);
        });

        if (app_type() == 'saas') {
            DB::table('companies')
                ->where('is_global', 1)
                ->update([
                    'white_label_completed' => env('APP_ENV') == 'production' ? 1 : 0
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('white_label_completed');
        });
    }
}
