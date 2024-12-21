<?php
include 'standards_angle.php';
// на входе высота полки, ширина полки, толщина стенки в мм и стандарт уголка
$height = (int)$argv[1];
$width = (int)$argv[2];
$wallThickness = (float)$argv[3];
$standard = $argv[4] ?? null;

$availableStandards = [
    'ГОСТ_8509-93', 'ГОСТ_8510-86', 'DIN_EN_10056-1-1998'
];

if (!in_array($standard, $availableStandards)) {
    exit (sprintf("Стандарт %s не поддерживается, Выберите из (%s).\n", $standard, implode(', ', $availableStandards)));
}

function getPerimeter(int $height, int $width, int|float $wallThickness, string $standard): float
{
    $perimeter = ($height + $width);
    $outerRadius = getRadius($standard, $height, $width, $wallThickness);
    $innerRadius = getRadius($standard, $height, $width, $wallThickness, false);
    $radiusCoefficient = $outerRadius * (0.5 * pi() - 2);
    $perimeter += $radiusCoefficient;
    $radiusCoefficient = $innerRadius * (0.5 * pi() - 2);
    $perimeter += 2 * $radiusCoefficient;
    $perimeter += $height + $width;

    return $perimeter;
}

function getSectionalArea(int $height, int $width, int|float $wallThickness, string $standard): float
{
    $area = ($width + $height - $wallThickness) * $wallThickness;
    $outerRadius = getRadius($standard, $height, $width, $wallThickness);
    $innerRadius = getRadius($standard, $height, $width, $wallThickness, false);
    $radiusCoefficient = $outerRadius * $outerRadius * (1 - 0.25 * pi());
    $area += $radiusCoefficient;
    $radiusCoefficient = $innerRadius * $innerRadius * (0.25 * pi() - 1);
    $area += 2 * $radiusCoefficient;

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

function getRadius(string $standard, int $height, int $width, int|float $wallThickness, bool $isOuter = true): float
{
    global $standardGost8509;
    global $standardDin_EN_10056_1_1998;

    $key = implode(' x ', [$height, $width, str_replace('.', ',', (string)($wallThickness))]);
    switch ($standard) {
        case 'ГОСТ_8509-93':
        case 'ГОСТ_8510-86':
            if (isset($standardGost8509[$key])) {
                return (float)$standardGost8509[$key][$isOuter ? 'R' : 'r'] ?? 0;
            }
            break;

        case 'DIN_EN_10056-1-1998':
            if (isset($standardDin_EN_10056_1_1998[$key])) {
                return (float)$standardDin_EN_10056_1_1998[$key][$isOuter ? 'R' : 'r'] ?? 0;
            }
            break;

    }
    exit(sprintf("Уголок с параметрами %s не найден.\n", $key));
}

$perimeter = getPerimeter($height, $width, $wallThickness, $standard);// o
$sectionalArea = getSectionalArea($height, $width, $wallThickness, $standard);//t
$surfaceAreaPerMeter = getSurfaceAreaPerMeter($perimeter);//eu
$surfaceAreaPerTon = getSurfaceAreaPerTon($sectionalArea, $surfaceAreaPerMeter); //ppt

echo sprintf("Приведенная толщина металла: %.3f мм\n", $sectionalArea / $perimeter);
echo sprintf("Обогреваемый периметр: %.3f мм\n", $perimeter);
echo sprintf("Площадь поверхности / 1м: %.3f м2\n", $surfaceAreaPerMeter);
echo sprintf("Площадь поверхности / 1т: %.3f м2\n", $surfaceAreaPerTon);


exit();
