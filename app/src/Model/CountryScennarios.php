<?php

namespace App\Model;

use App\Model\Country;
use App\Model\CountryRepository;
use App\Model\Exceptions\CountryNotFoundException;
use App\Model\Exceptions\InvalidCountryCodeException;
use App\Model\Exceptions\InvalidCountryDataException;
use Exception;

class CountryScenarios
{
    private CountryRepository $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    /**
     * Получение списка всех стран.
     *
     * @return array Массив объектов Country.
     */
    public function GetAll(): array
    {
        return $this->countryRepository->GetAll();
    }

    /**
     * Получение страны по коду.
     *
     * @param string $code Код страны (двухбуквенный, трехбуквенный или числовой).
     *
     * @return Country Объект Country, если страна найдена.
     *
     * @throws CountryNotFoundException Если страна с указанным кодом не найдена.
     * @throws InvalidCountryCodeException Если код страны имеет неверный формат.
     */
    public function Get(string $code): Country
    {
        $country = $this->countryRepository->Get($code);

        if (!$country) {
            // Проверяем формат кода, чтобы выкинуть правильное исключение
            if (strlen($code) == 2 || strlen($code) == 3 || is_numeric($code)) {
                throw new CountryNotFoundException("Страна с кодом $code не найдена");
            } else {
                throw new InvalidCountryCodeException("Невалидный код страны: $code");
            }
        }

        return $country;
    }

    /**
     * Сохранение новой страны.
     *
     * @param Country $country Объект Country для сохранения.
     *
     * @throws InvalidCountryDataException Если данные страны не валидны (коды не уникальны, названия пустые, население/площадь отрицательные).
     */
    public function Store(Country $country): void
    {
        // Валидация данных
        if (empty($country->shortName) || empty($country->fullName)) {
            throw new InvalidCountryDataException("Название страны не может быть пустым.");
        }

        if (!in_array(strlen($country->isoAlpha2), [2, 3]) || !in_array(strlen($country->isoAlpha3), [3, 2]) || !is_numeric($country->isoNumeric)) {
            throw new InvalidCountryCodeException("Неверный формат кода страны.");
        }

        if ($country->population < 0 || $country->square < 0) {
            throw new InvalidCountryDataException("Население и площадь должны быть положительными числами.");
        }

        // Если все данные валидны, передаем в хранилище для сохранения
        $this->countryRepository->Store($country);
    }


    /**
     * Редактирование страны по коду.
     *
     * @param string $code Код страны (двухбуквенный, трехбуквенный или числовой).
     * @param Country $country Объект Country с новыми данными.
     *
     * @throws CountryNotFoundException Если страна с указанным кодом не найдена.
     * @throws InvalidCountryDataException Если новые данные страны не валидны.
     * @throws InvalidCountryCodeException Если код страны имеет неверный формат.
     */
    public function Edit(string $code, Country $country): void
    {
        // Валидация данных
        if (empty($country->shortName) || empty($country->fullName)) {
            throw new InvalidCountryDataException("Название страны не может быть пустым.");
        }

        if ($country->population < 0 || $country->square < 0) {
            throw new InvalidCountryDataException("Население и площадь должны быть положительными числами.");
        }

        // Обновляем страну
        $this->countryRepository->Update($code, $country);
    }


    /**
     * Удаление страны по коду.
     *
     * @param string $code Код страны (двухбуквенный, трехбуквенный или числовой).
     *
     * @throws CountryNotFoundException Если страна с указанным кодом не найдена.
     * @throws InvalidCountryCodeException Если код страны имеет неверный формат.
     */
    public function Delete(string $code): void
    {
        // Проверка валидности кода
        if (!preg_match('/^([A-Z]{2}|[A-Z]{3}|\d{3})$/', $code)) {
            throw new InvalidCountryCodeException('Invalid code');
        }

        // Вызов метода удаления из CountryRepository
        $this->countryRepository->Delete($code);

        // Проверка на существование страны
        if ($this->countryRepository->Get($code) !== null) {
            throw new CountryNotFoundException('Country not found');
        }
    }

}
