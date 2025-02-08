<?php
namespace App\Model\Exceptions;

use Exception;

class CountryNotFoundException extends Exception {
    /**
     * Конструктор исключения.
     *
     * @param string $message Сообщение об ошибке.
     * @param int $code Код ошибки.
     * @param Exception|null $previous Предыдущее исключение, если это исключение было вызвано другим.
     */
    public function __construct(string $message = "Country not found", int $code = 0, Exception $previous = null)
    {
        // Вызов конструктора родительского класса
        parent::__construct($message, $code, $previous);
    }
}
