<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Auto Presenter.
 *
 * (c) Shawn McCool <shawn@heybigname.com>
 * (c) Graham Campbell <hello@gjcampbell.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace McCool\LaravelAutoPresenter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed decorate(mixed $subject)
 * @method static void register(\McCool\LaravelAutoPresenter\Decorators\DecoratorInterface $decorator)
 * @method static \McCool\LaravelAutoPresenter\Decorators\DecoratorInterface[] getDecorators()
 */
class AutoPresenter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'autopresenter';
    }
}
