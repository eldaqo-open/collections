<?php

declare(strict_types=1);

use SebastianBergmann\Comparator\Factory;
use Tests\Support\DictionaryComparator;

Factory::getInstance()->register(new DictionaryComparator());
