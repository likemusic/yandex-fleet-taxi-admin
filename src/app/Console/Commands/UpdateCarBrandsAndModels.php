<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Likemusic\YandexFleetTaxiClient\Contracts\ClientInterface as YandexClientInterface;
use Likemusic\YandexFleetTaxiClient\Contracts\LanguageInterface;
use Likemusic\YandexFleetTaxi\LeadMonitor\GoogleSpreadsheet\app\Console\Commands\UpdateCarBrandsAndModels\CarBrandsGenerator;
use Likemusic\YandexFleetTaxi\LeadMonitor\GoogleSpreadsheet\app\Console\Commands\UpdateCarBrandsAndModels\CarBrandModelsGenerator;

class UpdateCarBrandsAndModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cars:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate cars brands and models data by Yandex-provided data.';

    /**
     * @var CarBrandsGenerator
     */
    private $carBrandsGenerator;

    /**
     * @var CarBrandModelsGenerator
     */
    private $carBrandModelsGenerator;

    /**
     * @var YandexClientInterface
     */
    private $yandexClient;

    /**
     * @var string
     */
    private $yandexLogin;

    /**
     * @var string
     */
    private $yandexPassword;

    /**
     * @var string
     */
    private $parkId;

    /**
     * Create a new command instance.
     *
     * @param CarBrandsGenerator $carBrandsGenerator
     * @param CarBrandModelsGenerator $carBrandModelsGenerator
     * @param YandexClientInterface $yandexClient
     * @param string $yandexLogin
     * @param string $yandexPassword
     * @param string $parkId
     */
    public function __construct(
        CarBrandsGenerator $carBrandsGenerator,
        CarBrandModelsGenerator $carBrandModelsGenerator,
        YandexClientInterface $yandexClient,
        string $yandexLogin,
        string $yandexPassword,
        string $parkId
    ) {
        $this->carBrandsGenerator = $carBrandsGenerator;
        $this->carBrandModelsGenerator = $carBrandModelsGenerator;
        $this->yandexClient = $yandexClient;
        $this->yandexLogin = $yandexLogin;
        $this->yandexPassword = $yandexPassword;
        $this->parkId = $parkId;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $yandexClient = $this->yandexClient;
        $parkId = $this->parkId;//todo: проверить нужен ли действительно parkId, или можно получить данные и без него

        $this->initYandexClient($yandexClient, $this->yandexLogin, $this->yandexPassword);
        $brands = $this->generateBrands($yandexClient, $parkId);
        $this->generateModelsForBrands($yandexClient, $brands);
    }

    private function generateModelsForBrands($yandexClient, $brands)
    {
        $this->carBrandModelsGenerator->generateBrandsModels($yandexClient, $brands);
    }

    private function generateBrands(YandexClientInterface $yandexClient, string $parkId)
    {
        return $this->carBrandsGenerator->generateBrands($yandexClient, $parkId);
    }

    private function initYandexClient(YandexClientInterface $yandexClient, $login, $password)
    {
        $yandexClient->login($login, $password);
        $yandexClient->getDashboardPageData();
        $yandexClient->changeLanguage(LanguageInterface::RUSSIAN);
    }
}