<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
            $table->string('bill')->nullable()->default(NULL);
            $table->bigInteger('expense_category_id')->unsigned()->nullable();
            $table->foreign('expense_category_id')->references('id')->on('expense_categories')->onDelete('set null')->onUpdate('cascade');
            $table->float('amount', 8, 2);
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->string('notes', 1000)->nullable()->default(null);
            $table->date('date');
            $table->timestamps();
        });

        // Adding currencies
        // Creating only for non-saas
        // if (app_type() == 'non-saas') {
        //     $allCompanies = Company::all();

        //     foreach ($allCompanies as $allCompany) {
        //         $currencyId = DB::table('currencies')->insertGetId([
        //             'company_id' => $allCompany->id,
        //             'name' => 'Dollar',
        //             'code' => 'USD',
        //             'symbol' => '$',
        //             'position' => 'front',
        //             'is_deletable' => false,
        //         ]);

        //         $allCompany->currency_id = $currencyId;
        //         $allCompany->save();
        //     }
        // }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
