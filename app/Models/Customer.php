<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Rules\IsValidEmailRfc2822;

class Customer extends Model
{
    use HasFactory;

    /**
     * Таблица БД, ассоциированная с моделью.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * Соединение с БД, которое должна использовать модель.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * PK.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Fillable columns.
     *
     * @var string
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'age',
        'location_id',
    ];

    /**
     * Правила валидации
     *
     * @var array
     */
    protected $rules = [
        'age' => 'required|digits_between:18,99',
    ];

}
