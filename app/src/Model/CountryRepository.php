<?php

namespace App\Model;

interface CountryRepository
{
    /**
     * Получение списка всех стран.
     *
     * @return array Массив объектов Country.
     */
    public function GetAll(): array;

    /**
     * Получение страны по коду.
     *
     * @param string $code Код страны (двухбуквенный, трехбуквенный или числовой).
     *
     * @return Country|null Объект Country, если страна найдена, иначе null.
     */
    public function Get(string $code): ?Country;

    /**
     * Сохранение новой страны.
     *
     * @param Country $country Объект Country для сохранения.
     */
    public function Store(Country $country): void;

    /**
     * Редактирование страны по коду.
     *
     * @param string $code Код страны (двухбуквенный, трехбуквенный или числовой).
     * @param Country $country Объект Country с новыми данными.
     */
    public function Edit(string $code, Country $country): void;
    public function Update(string $code, Country $country): void;
    /**
     * Удаление страны по коду.
     *
     * @param string $code Код страны (двухбуквенный, трехбуквенный или числовой).
     */
    public function Delete(string $code): void;
}
