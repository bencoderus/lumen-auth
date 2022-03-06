<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Cache;

class DummyController extends Controller
{
    public const CACHE_KEY = 'dummy_data';

    public const TRANSACTION_TYPES = ['credit', 'debit'];

    public function getDummyData(Request $request)
    {
        $data = $this->filterData($this->getData(), $request);

        return $this->okResponse("Data retrieved successfully", $data);
    }

    public function getNewDummyData(Request $request)
    {
        $data = $this->filterData($this->getData(), $request);

        return response()->json($data);
    }

    private function filterData(Collection $data, Request $request): Collection
    {
        if ($search = $request->search) {
            $data = $this->search($data, $search);
        }

        if ($startDate = $request->start_date) {
            $data = $this->filterByStartDate($data, $startDate);
        }

        if ($endDate = $request->end_date) {
            $data = $this->filterByEndDate($data, $endDate);
        }

        $type = Str::lower($request->type);

        if ($request->has('type') && in_array($type, self::TRANSACTION_TYPES)) {
            $data = $this->filerByType($data, $type);
        }

        return $data;
    }

    private function filerByType(Collection $data, string $type)
    {
        return $data->filter(function ($item) use ($type) {
            return $item['type'] === $type;
        })->values();
    }

    private function filterByStartDate(Collection $data, string $startDate)
    {
        return $data->filter(function ($item) use ($startDate) {
            $recordDate = Carbon::parse($item['timestamp']);
            $startDate = Carbon::parse($startDate);

            return $recordDate->gte($startDate);
        })->values();
    }

    private function filterByEndDate(Collection $data, string $endDate)
    {
        return $data->filter(function ($item) use ($endDate) {
            $recordDate = Carbon::parse($item['timestamp']);
            $endDate = Carbon::parse($endDate);

            return $recordDate->lte($endDate);
        })->values();
    }

    public function refresh(Request $request)
    {
        Cache::forget(self::CACHE_KEY);
        $data = $this->generateData();

        $this->saveInCache($data);

        return $this->okResponse("Data regenerated successfully");
    }

    private function search(Collection $data, string $search): Collection
    {
        $search = Str::lower($search);

        return $data->filter(function ($data) use ($search) {
            return Str::contains(Str::lower($data['name']), $search)
                || Str::contains(Str::lower($data['narrations']), $search)
                || Str::contains(Str::lower($data['type']), $search);
        })->values();
    }

    private function getData(): Collection
    {
        $cached = Cache::get(self::CACHE_KEY);

        if ($cached) {
            return $cached;
        }

        $data = $this->generateData();

        $this->saveInCache($data);

        return $data;
    }

    private function saveInCache($data)
    {
        Cache::put(self::CACHE_KEY, $data, Carbon::now()->addHours(6));
    }

    private function generateData(): Collection
    {
        $faker = Container::getInstance()->make(Generator::class);
        $data = collect([]);

        for ($i = 0; $i < 50; $i++) {
            $reduce = rand(100, 100000);
            $id = $i + 1;

            $generated = [
                'id' => $id,
                'timestamp' => Carbon::now()->subHours($reduce)->toDateTimeString(),
                'name' => $faker->name,
                'narrations' => $faker->randomElement(['bills', 'hangout', 'fees']),
                'type' => $faker->randomElement(['credit', 'debit']),
                'amount' => rand(100, 100000),
                'balance' => rand(100, 1000000)
            ];

            $data->push($generated);
        }

        return $data;
    }
}
