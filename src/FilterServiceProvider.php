<?php declare(strict_types=1);


namespace Dpsoft\NovaMultiselectFilter;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FilterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Nova::serving(function (ServingNova $event): void {
            Nova::translations([
                'novaMultiselectFilter.maxElements' => 'بیشتر از :max انتخاب شده است.',
                'novaMultiselectFilter.noResult' => 'نتیجه ای یافت نشد.',
                'novaMultiselectFilter.noOptions' => 'لیست خالی است.',
                'novaMultiselectFilter.limitText' => 'و :count بیشتر',
                'novaMultiselectFilter.placeholder' => 'انتخاب گزینه(ها)',
                'novaMultiselectFilter.nItemsSelected' => ':count آیتم انتخاب شده'
            ]);
            Nova::script('nova-multiselect-filter', __DIR__ . '/../dist/js/filter.js');
        });

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
