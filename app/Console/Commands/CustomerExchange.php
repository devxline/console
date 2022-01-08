<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Shasoft\Console\Console;

use Validator;
use App\Models\Customer;
use App\Models\Location;
use App\Rules\IsValidEmailRfc2822;
use App\Rules\IsValidEmailDns;

class CustomerExchange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:exchange {path?}';

    /**
     * The console command help text.
     *
     * @var string
     */
    protected $help = 'Затянуть данные из random.csv';

    /**
     * The timestamp handling
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка из csv-файла';

    /**
     * Create a new command instance.
     *
     * @void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Console printer
     * @param string $string - string for console output
     * @param color $color - text output color
     * @void
     */
    public function consoleWriteLine($line, $color = 'white')
    {
        Console::color($color)->writeln($line);
        return false;
    }

    /**
     * Get data from file
     * @param string $from - file for csv parsing
     * @return array
     */
    public function getCsvData($from)
    {
        $lines = file($from);
        $utf8_lines = array_map('utf8_encode', $lines);
        $array = array_map(function ($str) {
            $prepare = str_getcsv($str, ';');
            $tmp = explode(',', $prepare[0]);
            if ($tmp[1] != 'name') {
                $this->consoleWriteLine('Извлечение ' . $tmp[1], 'blue');
            }
            return [
                'source' => $prepare,
                'prepare' => $tmp,
            ];
        }, $utf8_lines);
        array_shift($array);
        return $array ?? [];
    }

    /**
     * Data Prepare Handler
     * @param array $arr - input data for handling
     * @array
     */
    public function dataPrepareHandler($arr)
    {
        $box = [];
        foreach ($arr as $key => $person) {
            $tmp = $person['prepare'];

            $name = explode(' ', $tmp[1])[0];
            $surname = explode(' ', $tmp[1])[1];

            $item = [
                'name' => $name,
                'surname' => $surname,
                'email' => $tmp[2],
                'age' => intval(self::onlyNum($tmp[3])),
            ];

            if (!empty($tmp[4])) {
                $country = Location::where('name', $tmp[4])->first();
                if ($country) {
                    $countryCode = $country->id;
                } else {
                    $countryCode = null;
                }

            } else {
                $countryCode = null;
            }

            $item['location_id'] = $countryCode;

            $line = 'Обработка ';
            $line .= $tmp[1];
            $line .= ' [';
            $line .= $countryCode ? $countryCode : 'Unknown';
            $line .= ', ';
            $line .= $item['age'];
            $line .= '] : ';
            $line .= $item['email'];
            $this->consoleWriteLine($line, 'yellow');

            $arr[$key]['prepare'] = $item;
            $arr[$key]['errors'] = [];

            $box[] = $arr[$key];
        }

        return $box;
    }

    /**
     * Numeric handler
     * @param int $num - input number for handling
     * @int $num
     */
    static function onlyNum($num)
    {
        if(is_array($num)){
            foreach($num AS $i => $v){
                $num[$i] = self::onlyNum($v);
            }
        }else{
            $num = preg_replace('/[^\d]+/', '', $num);
        }
        return $num;
    }

    /**
     * Error report writer
     * @param path $path - output file for errors reporting
     * @param data $data - source data for csv file
     * @void
     */
    public function createErrorReport($path, $data)
    {
        $fp = fopen($path, 'w');

        // Loop through file pointer and a line
        foreach ($data as $fields) {
            fputcsv($fp, $fields, ';');
        }
        fclose($fp);

        $line = 'Отчет создан - ';
        $line .= $path;
        $this->consoleWriteLine($line, 'white');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $arguments = $this->arguments();

        $preparePath = public_path();
        $preparePath .= '/random.csv';

        $reportPath = public_path();
        $reportPath .= '/report.csv';

        $path = $arguments['path'] ?? $preparePath;

        if(file_exists($path)){
            $data = $this->getCsvData($path);
            if ($dataReady = $data && is_array($data) && sizeof($data) > 0) {
                $prepare = $this->dataPrepareHandler($data);
                foreach ($prepare as $key => $value) {
                    $tmp = $value['prepare'];

                    $validator = Validator::make($tmp, [
                        'age' => 'integer|min:18|max:99',
                        'email'=>'required|email',
                        'email' => new IsValidEmailRfc2822,
                        'email' => new IsValidEmailDns,
                    ]);

                    $consoleOutput = $tmp['name'];
                    $consoleOutput .= ', ';
                    $consoleOutput .= $tmp['age'];
                    $consoleOutput .= ' [';
                    $consoleOutput .= $tmp['email'];
                    $consoleOutput .= ']';

                    if($validator->fails()){
                        $errors = array_keys(json_decode(json_encode($validator->messages()), true));
                        $prepare[$key]['errors'] = $errors;

                        $line = 'Ошибка валидации ';
                        $line .= $consoleOutput;
                        $line .= ' - ';
                        $line .= implode(', ', $errors);

                        $color = 'red';
                    } else {
                        $emailUniqueValidation = Validator::make($tmp, [
                            'email'=>'unique:customers',
                        ]);
                        if($emailUniqueValidation->fails()){
                            $line = 'Дубликат - ';
                            $line .= $consoleOutput;
                            $line .= '. Не добавлено.';

                            $color = 'purple';
                        } else {
                            $customer = new Customer($tmp);
                            if($customer->save()) {
                                $line = 'Добавлено ';
                                $line .= $consoleOutput;

                                $color = 'green';
                            } else {
                                // other error exception handling
                            }
                        }
                    }
                    $this->consoleWriteLine($line, $color);
                }

                $invalidRecords = array_filter(
                    array_map(function ($arr) {
                        if (sizeof($arr['errors']) > 0) {
                            return [
                                'source' => $arr['source'][0],
                                'errors' => implode(', ', $arr['errors']),
                            ];
                        }
                    }, $prepare)
                );
                array_unshift($invalidRecords, ['source string', 'error']);

                if (sizeof($invalidRecords) > 0) {
                    $reportPath = public_path();
                    $reportPath .= '/report.csv';

                    $this->consoleWriteLine('Создание отчета для Excel', 'white');
                    $this->createErrorReport($reportPath, $invalidRecords);
                }
                $this->consoleWriteLine("\n");
            } else {
                throw new \Exception('Oops, something went wrong!');
            }
        } else {
            throw new \Exception('File random.csv was not found');
        }

        return Command::SUCCESS;
    }
}
