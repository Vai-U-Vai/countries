<?php

namespace App\Rdb;

use App\Model\Country;
use App\Model\CountryRepository;
use App\Model\Exceptions\CountryNotFoundException;
use Exception;
use PDO;
use PDOException;

class CountryStorage implements CountryRepository
{
    private SqlHelper $sqlHelper;
    private PDO $db;

    public function __construct(SqlHelper $sqlHelper)
    {
        $this->sqlHelper = $sqlHelper;
        $this->db = $this->sqlHelper->openDbConnection(); // Создаем соединение с БД в конструкторе
    }

    /**
     * Получение списка всех стран.
     *
     * @return array Массив объектов Country.
     */
    public function GetAll(): array
    {
        $countries = [];
        $stmt = $this->db->query("SELECT * FROM countries_t");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $countries[] = $this->createCountryFromRow($row);
        }
        return $countries;
    }

    /**
     * Получение страны по коду.
     *
     * @param string $code Код страны (двухбуквенный, трехбуквенный или числовой).
     *
     * @return Country|null Объект Country, если страна найдена, иначе null.
     */
    public function Get(string $code): ?Country
    {
        if (strlen($code) == 2) {
            return $this->GetByIsoAlpha2($code);
        } elseif (strlen($code) == 3) {
            return $this->GetByIsoAlpha3($code);
        } elseif (is_numeric($code)) {
            return $this->GetByIsoNumeric($code);
        } else {
            return null; // Невалидный код
        }
    }

    /**
     * Получение страны по ISO Alpha2 коду.
     *
     * @param string $isoAlpha2 ISO Alpha2 код страны.
     *
     * @return Country|null Объект Country, если страна найдена, иначе null.
     */
    public function GetByIsoAlpha2(string $isoAlpha2): ?Country
    {
        $stmt = $this->db->prepare("SELECT * FROM countries_t WHERE isoAlpha2 = :isoAlpha2");
        $stmt->execute(['isoAlpha2' => $isoAlpha2]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createCountryFromRow($row) : null;
    }

    /**
     * Получение страны по ISO Alpha3 коду.
     *
     * @param string $isoAlpha3 ISO Alpha3 код страны.
     *
     * @return Country|null Объект Country, если страна найдена, иначе null.
     */
    public function GetByIsoAlpha3(string $isoAlpha3): ?Country
    {
        $stmt = $this->db->prepare("SELECT * FROM countries_t WHERE isoAlpha3 = :isoAlpha3");
        $stmt->execute(['isoAlpha3' => $isoAlpha3]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createCountryFromRow($row) : null;
    }

    /**
     * Получение страны по ISO Numeric коду.
     *
     * @param string $isoNumeric ISO Numeric код страны.
     *
     * @return Country|null Объект Country, если страна найдена, иначе null.
     */
    public function GetByIsoNumeric(string $isoNumeric): ?Country
    {
        $stmt = $this->db->prepare("SELECT * FROM countries_t WHERE isoNumeric = :isoNumeric");
        $stmt->execute(['isoNumeric' => $isoNumeric]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createCountryFromRow($row) : null;
    }

    /**
     * Сохранение новой страны.
     *
     * @param Country $country Объект Country для сохранения.
     */
    public function Store(Country $country): void
    {
        // Проверка уникальности наименования и кода страны
        if ($this->checkIfCountryExists($country)) {
            throw new Exception('Страна с таким кодом или наименованием уже существует.');
        }

        // Подготовка SQL-запроса для вставки новой страны
        $stmt = $this->db->prepare("INSERT INTO countries_t (shortName, fullName, isoAlpha2, isoAlpha3, isoNumeric, population, square) 
                                VALUES (:shortName, :fullName, :isoAlpha2, :isoAlpha3, :isoNumeric, :population, :square)");

        $stmt->execute([
            'shortName' => $country->shortName,
            'fullName' => $country->fullName,
            'isoAlpha2' => $country->isoAlpha2,
            'isoAlpha3' => $country->isoAlpha3,
            'isoNumeric' => $country->isoNumeric,
            'population' => $country->population,
            'square' => $country->square,
        ]);
    }
    public function Update(string $code, Country $country): void
    {
        // Проверяем, существует ли страна с данным кодом
        $existingCountry = $this->Get($code);

        if (!$existingCountry) {
            throw new CountryNotFoundException("Страна с кодом {$code} не найдена.");
        }

        // Обновляем только те поля, которые можно изменять
        $stmt = $this->db->prepare("UPDATE countries_t
                            SET shortName = :shortName,
                                fullName = :fullName,
                                population = :population,
                                square = :square
                            WHERE isoAlpha2 = :code1
                               OR isoAlpha3 = :code2
                               OR isoNumeric = :code3");

        $stmt->execute([
            'shortName' => $country->shortName,
            'fullName' => $country->fullName,
            'population' => $country->population,
            'square' => $country->square,
            'code1' => $code,
            'code2' => $code,
            'code3' => $code,
        ]);

    }
    private function checkIfCountryExists(Country $country): bool
    {
        // Проверка уникальности только кодов (isoAlpha2, isoAlpha3, isoNumeric)
        $stmt = $this->db->prepare("SELECT COUNT(*) 
                                FROM countries_t 
                                WHERE isoAlpha2 = :isoAlpha2 
                                   OR isoAlpha3 = :isoAlpha3 
                                   OR isoNumeric = :isoNumeric");

        $stmt->execute([
            'isoAlpha2' => $country->isoAlpha2,
            'isoAlpha3' => $country->isoAlpha3,
            'isoNumeric' => $country->isoNumeric
        ]);

        return $stmt->fetchColumn() > 0;
    }




    /**
     * Редактирование страны по коду.
     *
     * @param string $code Код страны (двухбуквенный, трехбуквенный или числовой).
     * @param Country $country Объект Country с новыми данными.
     */
    public function Edit(string $code, Country $country): void
    {
        // Проверим, существует ли страна с данным кодом
        $query = 'SELECT * FROM countries_t WHERE isoAlpha2 = :code OR isoAlpha3 = :code OR isoNumeric = :code LIMIT 1';
        $stmt = $this->db->prepare($query); // Используем sqlHelper для подготовки запроса
        $stmt->bindValue(':code', $code);
        $stmt->execute();

        $existingCountry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingCountry) {
            throw new CountryNotFoundException("Страна с кодом {$code} не найдена.");
        }

        // Здесь будет обновление данных страны, но без изменения кодов
        $query = 'UPDATE countries_t SET shortName = :shortName, fullName = :fullName, population = :population, square = :square 
              WHERE isoAlpha2 = :code OR isoAlpha3 = :code OR isoNumeric = :code';

        $stmt = $this->db->prepare($query); // Используем sqlHelper для подготовки запроса
        $stmt->bindValue(':shortName', $country->shortName);
        $stmt->bindValue(':fullName', $country->fullName);
        $stmt->bindValue(':population', $country->population);
        $stmt->bindValue(':square', $country->square);
        $stmt->bindValue(':code', $code);
        $stmt->execute();
    }


    /**
     * Удаление страны по коду.
     *
     * @param string $code Код страны (двухбуквенный, трехбуквенный или числовой).
     */
    public function Delete(string $code): void
    {
        $stmt = $this->db->prepare("DELETE FROM countries_t
                               WHERE isoAlpha2 = :code1
                                  OR isoAlpha3 = :code2
                                  OR isoNumeric = :code3");

        $stmt->execute([
            'code1' => $code,
            'code2' => $code,
            'code3' => $code,
        ]);
    }


    /**
     * Создает объект Country из строки данных из базы данных.
     *
     * @param array $row Строка данных из базы данных.
     *
     * @return Country Объект Country.
     */
    private function createCountryFromRow(array $row): Country
    {
        $country = new Country();
        $country->shortName = $row['shortName'];
        $country->fullName = $row['fullName'];
        $country->isoAlpha2 = $row['isoAlpha2'];
        $country->isoAlpha3 = $row['isoAlpha3'];
        $country->isoNumeric = $row['isoNumeric'];
        $country->population = $row['population'];
        $country->square = $row['square'];
        return $country;
    }
}
