<?php

declare(strict_types=1);

namespace Eldaqo\Collections;

enum SortDirection: int
{
    case ASCENDING = 1;
    case DESCENDING = -1;
}
