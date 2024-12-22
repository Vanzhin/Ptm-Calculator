<?php

include 'standards_channel.php';
// на входе стандарт швеллера и марку швеллера
$standard = $argv[1];
$type = $argv[2];
$isTop = $argv[3] === 'true';
$isBottom = $argv[4] === 'true';
$isLeft = $argv[5] === 'true';
$isRight = $argv[6] === 'true';

if (!((int)$isTop + (int)$isBottom + (int)$isLeft + (int)$isRight)) {
    exit("Швеллер не обогревается.\n");
}
$availableStandards = [
    'ГОСТ_8240-97', 'DIN_1026', 'ГОСТ_8278-83'
];


if (!in_array($standard, $availableStandards)) {
    exit (sprintf("Стандарт не поддерживается %s, Выберите из (%s).\n", $standard, implode(', ', $availableStandards)));
}

function getDimensions(string $standard, string $type): array
{
    global $standardGost8240;
    global $standardDin1026;
    switch ($standard) {
        case 'ГОСТ_8240-97':
        case 'ГОСТ_8278-83':
            if (isset($standardGost8240[$type])) {
                return array_values(array_map(fn($item) => (float)$item, $standardGost8240[$type]));
            }
            break;

        case 'DIN_1026':
            if (isset($standardDin1026[$type])) {
                return array_values(array_map(fn($item) => (float)$item, $standardDin1026[$type]));
            }
            break;
    }
    exit(sprintf("Швеллер %s не найден.\n", $type));
}

function getPerimeter(
    float $height,
    float $width,
    float $wallThickness,
    float $innerRadius,
    float $outerRadius,
    float $coefficient,
    bool  $isTop = true, bool $isBottom = true, bool $isLeft = true, bool $isRight = true

): float
{
    $perimeter = 0;
    if ($isTop) {
        $perimeter += $width;
    }
    if ($isBottom) {
        $perimeter += $width;
    }
    if ($isLeft) {
        $perimeter += $height;
    }
    if ($isRight) {
        $perimeter += $height + 2 * ($width - $wallThickness) * (1 / cos(atan($coefficient)) - $coefficient);
        $correction = 2 * $innerRadius * pi() * 2 * (90 - (90 + qj(atan($coefficient))) / 2) / 360
            - 2 * $innerRadius * tan(wp(90 - (90 + qj(atan($coefficient))) / 2));
        $perimeter += 2 * $correction;
        $correction = 2 * $outerRadius * pi() * 2 * (90 - (90 + qj(atan($coefficient))) / 2) / 360
            - 2 * $outerRadius * tan(wp(90 - (90 + qj(atan($coefficient))) / 2));
        $perimeter += 2 * $correction;
    }

    return $perimeter;
}

function getSectionalArea(
    float $height,
    float $width,
    float $wallThickness,
    float $shelfThickness,
    float $innerRadius,
    float $outerRadius,
    float $coefficient): float
{
    $area = $height * $wallThickness + 2 * ($width - $wallThickness) * $shelfThickness;
    $correction = $innerRadius * $innerRadius / tan(wp((90 + qj(atan($coefficient))) / 2))
        - $innerRadius * $innerRadius * pi() * (90 - qj(atan($coefficient))) / 360;
    $area += 2 * $correction;
    $correction = 0 - $outerRadius * $outerRadius / tan(wp((90 + qj(atan($coefficient))) / 2))
        + $outerRadius * $outerRadius * pi() * (90 - qj(atan($coefficient))) / 360;
    $area += 2 * $correction;

    return $area;
}

function getSurfaceAreaPerMeter(float $perimeter): float
{
    return $perimeter / 1000;
}

function getSurfaceAreaPerTon(float $sectionalArea, float $surfaceAreaPerMeter): float
{
    return 1000 / ($sectionalArea / 1000 * 7.85) * $surfaceAreaPerMeter;
}

//вспомогательные функции как из скрипта
function qj(float $value): float
{
    return 180 * $value / pi();
}

function wp(float $value): float
{
    return $value / 180 * pi();
}

// формируем константы швеллера
list(
    $height,//высота h
    $width, // ширина полки b
    $wallThickness, //толщина стенки s,
    $shelfThickness, //толщина полки t,
    $innerRadius, //внутренний радиус R,
    $outerRadius, //внешний радиус r,
    $coefficient,//коэффициент u
    ) = getDimensions($standard, $type);

$perimeter = getPerimeter(
    $height,
    $width,
    $wallThickness,
    $innerRadius,
    $outerRadius,
    $coefficient ?? 0,
    $isTop, $isBottom, $isLeft, $isRight
);// o
$sectionalArea = getSectionalArea($height, $width, $wallThickness, $shelfThickness, $innerRadius, $outerRadius, $coefficient);//t
$surfaceAreaPerMeter = getSurfaceAreaPerMeter($perimeter);//eu
$surfaceAreaPerTon = getSurfaceAreaPerTon($sectionalArea, $surfaceAreaPerMeter); //ppt

echo sprintf("Приведенная толщина металла: %.3f мм\n", $sectionalArea / $perimeter);
echo sprintf("Обогреваемый периметр: %.3f мм\n", $perimeter);
echo sprintf("Площадь поверхности / 1м: %.3f м2\n", $surfaceAreaPerMeter);
echo sprintf("Площадь поверхности / 1т: %.3f м2\n", $surfaceAreaPerTon);


exit();
