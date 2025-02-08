<?php

namespace App\Controller;

use App\Model\Country;
use App\Model\CountryScenarios;
use App\Model\Exceptions\InvalidCountryDataException;
use App\Model\Exceptions\InvalidCountryCodeException;
use App\Model\Exceptions\CountryNotFoundException;
use Exception;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/api/country', name: 'api_country')]
class CountryController extends AbstractController
{
    private CountryScenarios $countryScenarios;

    public function __construct(CountryScenarios $countryScenarios)
    {
        $this->countryScenarios = $countryScenarios;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        try {
            $countries = $this->countryScenarios->GetAll();
            return $this->json($countries); // Symfony автоматически сериализует массив объектов Country в JSON
        } catch (\Exception $e) {
            // Обработка других исключений, если они могут возникнуть (например, ошибка БД)
            return $this->json(['message' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{code}', name: 'get_by_code', methods: ['GET'])]
    public function getByCode(string $code): JsonResponse
    {
        try {
            $country = $this->countryScenarios->Get($code);
            return $this->json($country);
        } catch (CountryNotFoundException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (InvalidCountryCodeException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            // Логирование ошибки для отладки
            return $this->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверка данных
        if (!isset($data['shortName'], $data['fullName'], $data['isoAlpha2'], $data['isoAlpha3'], $data['isoNumeric'], $data['population'], $data['square'])) {
            return $this->json(['message' => 'Некоторые обязательные поля отсутствуют.'], Response::HTTP_BAD_REQUEST);
        }

        $country = new Country();
        $country->shortName = $data['shortName'];
        $country->fullName = $data['fullName'];
        $country->isoAlpha2 = $data['isoAlpha2'];
        $country->isoAlpha3 = $data['isoAlpha3'];
        $country->isoNumeric = $data['isoNumeric'];
        $country->population = $data['population'];
        $country->square = $data['square'];

        try {
            // Используем сценарий для сохранения страны
            $this->countryScenarios->Store($country);
            return $this->json([], Response::HTTP_NO_CONTENT); // 204 No Content
        } catch (InvalidCountryDataException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST); // 400 Bad Request
        } catch (InvalidCountryCodeException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST); // 400 Bad Request
        } catch (Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT); // 409 Conflict
        }
    }
    #[Route('/{code}', name: 'patch', methods: ['PATCH'])]
    public function patch(string $code, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверка, что хотя бы одно поле для обновления передано
        if (empty($data)) {
            return $this->json(['message' => 'Нет данных для обновления.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Получаем страну по коду
            $country = $this->countryScenarios->Get($code);

            // Обновляем данные страны (кроме кодов)
            if (isset($data['shortName'])) {
                $country->shortName = $data['shortName'];
            }
            if (isset($data['fullName'])) {
                $country->fullName = $data['fullName'];
            }
            if (isset($data['population'])) {
                $country->population = $data['population'];
            }
            if (isset($data['square'])) {
                $country->square = $data['square'];
            }

            // Сохраняем отредактированную страну
            $this->countryScenarios->Edit($code, $country);

            return $this->json($country); // Отправляем обновленный объект страны

        } catch (CountryNotFoundException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (InvalidCountryDataException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (InvalidCountryCodeException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/{code}', name: 'delete', methods: ['DELETE'])]
    public function deleteCountry(string $code): JsonResponse
    {
        try {
            // Удаление страны
            $this->countryScenarios->Delete($code);

            // Возвращаем ответ с кодом 204 No Content
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (CountryNotFoundException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (InvalidCountryCodeException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return new JsonResponse(['message' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
