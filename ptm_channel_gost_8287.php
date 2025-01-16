<?php
include 'standards_channel.php';
// на входе высота, ширина, толщина стенки в мм и стандарт профиля
$height = (int)$argv[1];
$width = (int)$argv[2];
$wallThickness = (float)$argv[3];
$standard = $argv[4] ?? null;
$isTop = $argv[5] === 'true';
$isBottom = $argv[6] === 'true';
$isLeft = $argv[7] === 'true';
$isRight = $argv[8] === 'true';
$radius = getRadius($standard, $height, $width, $wallThickness);


if (!((int)$isTop + (int)$isBottom + (int)$isLeft + (int)$isRight)) {
    exit("Швеллер не обогревается.\n");
}

$availableStandards = [
    'ГОСТ_8278-83'
];
if (!in_array($standard, $availableStandards)) {
    exit (sprintf("Стандарт %s не поддерживается, Выберите из (%s).\n", $standard, implode(', ', $availableStandards)));
}

function getPerimeter(
    int       $height,
    int       $width,
    int|float $wallThickness,
    int|float $radius,
    bool      $isTop = true, bool $isBottom = true, bool $isLeft = true, bool $isRight = true,
): float
{
    $perimeter = 0;
// расчет без радиуса
    if ($isTop) {
        $perimeter += $width - ($wallThickness + $radius);
    }
    if ($isBottom) {
        $perimeter += $width - ($wallThickness + $radius);
    }
    if ($isLeft) {
        $perimeter += $height - 2 * ($wallThickness + $radius);
    }
    if ($isRight) {
        $perimeter += $wallThickness * 2 + 2 * ($width - ($wallThickness + $radius))
            + $height - 2 * ($wallThickness + $radius)
            + $radius * pi();
    }
    // дополнительный расчет с радиусами, т.к. радиус может задвоиться, если не учитывать
    if ($isLeft && !$isTop && !$isBottom) {
        $perimeter += ($radius + $wallThickness) * pi();
        return $perimeter;
    }

    if ($isTop) {
        $perimeter += ($radius + $wallThickness) * 0.5 * pi();
    }
    if ($isBottom) {
        $perimeter += ($radius + $wallThickness) * 0.5 * pi();
    }

    return $perimeter;
}

function getSurfaceAreaPerMeter(float $perimeter): float
{
    return $perimeter / 1000;
}

function getSurfaceAreaPerTon(float $sectionalArea, float $surfaceAreaPerMeter): float
{
    return 1000 / ($sectionalArea / 1000 * 7.85) * $surfaceAreaPerMeter;
}

$perimeter = getPerimeter($height, $width, $wallThickness, $radius, $isTop, $isBottom, $isLeft, $isRight);
$sectionalArea = getSectionalArea($height, $width, $wallThickness, $radius);
$surfaceAreaPerMeter = getSurfaceAreaPerMeter($perimeter);
$surfaceAreaPerTon = getSurfaceAreaPerTon($sectionalArea, $surfaceAreaPerMeter);

echo sprintf("Приведенная толщина металла: %.3f мм\n", $sectionalArea / $perimeter);
echo sprintf("Обогреваемый периметр: %.3f мм\n", $perimeter);
echo sprintf("Площадь поверхности / 1м: %.3f м2\n", $surfaceAreaPerMeter);
echo sprintf("Площадь поверхности / 1т: %.3f м2\n", $surfaceAreaPerTon);


function getRadius(string $standard, int $height, int $width, int|float $wallThickness): int
{
    global $standardGost8278;
    $key = implode(' x ', [$height, $width, $wallThickness]);
    switch ($standard) {
        case 'ГОСТ_8278-83':
            if (isset($standardGost8278[$key])) {
                return (int)$standardGost8278[$key]['R'] ?? 0;
            }
            exit(sprintf("Швеллер с параметрами %s не найден.\n", $key));
        default :
            return 0;
    }
}

function getSectionalArea(int $height, int $width, int|float $wallThickness, int|float $radius): float
{
    // в мм кв
    return (($height - 2 * ($wallThickness + $radius)) * $wallThickness
            + 2 * (($width - ($wallThickness + $radius)) * $wallThickness
                + 0.25 * (pi() * ($radius + $wallThickness) * ($radius + $wallThickness) - (pi() * $radius * $radius))));
}

exit();
