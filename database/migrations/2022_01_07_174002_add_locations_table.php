<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationsTable extends Migration
{
    /**
     * Country names like "TH" => "Thailand"
     *
     * @var array
     */
    protected $countryNames = [];

    /**
     * Country names like [BD] => BGD
     *
     * @var array
     */
    protected $countryNamesIso3 = [];

    /**
     * Country names card
     *
     * @var array
     */
    protected $countryNamesCard = [];

    /**
     * Create a new command instance.
     *
     * @void
     */
    public function __construct()
    {
        $this->setCountryNames();
        $this->setCountryNamesIso3();
        $this->setCountryNamesCard();
    }

    /**
     * Create country card first component
     *
     * @void
     */
    public function setCountryNames()
    {
        $this->countryNames = json_decode(file_get_contents('http://country.io/names.json'), true);
        return;
    }

    /**
     * Create country card second component
     *
     * @void
     */
    public function setCountryNamesIso3()
    {
        $this->countryNamesIso3 = json_decode(file_get_contents('http://country.io/iso3.json'), true);
        return;
    }

    /**
     * Create country card complete
     *
     * @void
     */
    public function setCountryNamesCard()
    {
        $prepare = array_flip($this->countryNames);
        foreach ($prepare as $name => $code) {
            $prepare[$name] = $this->countryNamesIso3[$code];
        }
        $this->countryNamesCard = $prepare;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->engine = 'InnoDB';

                $table->increments('id')->comment('#');
                $table->string('name', 255)->comment('имя');
                $table->string('country_code', 3)->comment('код страны ISO3');
                //$table->timestamp('created_at')->useCurrent()->comment('создано');
                //$table->timestamp('updated_at')->useCurrent()->comment('изменено');
            });
            DB::statement("ALTER TABLE `locations` COMMENT 'локации'");

            foreach ($this->countryNamesCard as $name => $country_code) {
                DB::table('locations')->insert([
                    'name' => $name,
                    'country_code' => $country_code,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
