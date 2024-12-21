<?php

include 'standards_i_beam.php';
// на входе стандарт двутавра и марку двутавра
$standard = $argv[1];
$type = $argv[2];

$availableStandards = [
    'СТО_АСЧМ_20-93', 'ГОСТ_Р_57837-2017', 'ГОСТ_26020-83', 'ГОСТ_8239-89', 'ГОСТ_19425-74', 'DIN_1025'
];

if (!in_array($standard, $availableStandards)) {
    exit (sprintf("Стандарт %s не поддерживается, Выберите из (%s).\n", $standard, implode(', ', $availableStandards)));
}

function getDimensions(string $standard, string $type): array
{
    global $standardSto20_93;
    global $standardGostR57837_2017;
    global $standardGost26020_83;
    global $standardGost8239_89;
    global $standardGost19425_74;
    global $standardDin1025;

    switch ($standard) {
        case 'СТО_АСЧМ_20-93':
            if (isset($standardSto20_93[$type])) {
                return array_map(fn($item) => (float)$item, $standardSto20_93[$type]);
            }
            break;
        case 'ГОСТ_Р_57837-2017':
            if (isset($standardGostR57837_2017[$type])) {
                return array_map(fn($item) => (float)$item, $standardGostR57837_2017[$type]);
            }
            break;

        case 'ГОСТ_26020-83':
            if (isset($standardGost26020_83[$type])) {
                return array_map(fn($item) => (float)$item, $standardGost26020_83[$type]);
            }
            break;
        case 'ГОСТ_8239-89':
            if (isset($standardGost8239_89[$type])) {
                return array_map(fn($item) => (float)$item, $standardGost8239_89[$type]);
            }
            break;
        case 'ГОСТ_19425-74':
            if (isset($standardGost19425_74[$type])) {
                return array_map(fn($item) => (float)$item, $standardGost19425_74[$type]);
            }
            break;
        case 'DIN_1025':
            if (isset($standardDin1025[$type])) {
                return array_map(fn($item) => (float)$item, $standardDin1025[$type]);
            }
            break;
    }
    exit(sprintf("Двутавр %s не найден.\n", $type));
}

function getPerimeter(
    float $height,
    float $width,
    float $wallThickness,
    float $innerRadius,
    float $outerRadius,
    float $coefficient,

): float
{
    $perimeter = 0;
    $perimeter += $width;
    $perimeter += $width;
    $perimeter += $height + 1 * ($width - $wallThickness) * (1 / cos(atan($coefficient)) - $coefficient);
    $correction = 2 * $innerRadius * pi() * 2 * (90 - (90 + qj(atan($coefficient))) / 2) / 360
        - 2 * $innerRadius * tan(wp(90 - (90 + qj(atan($coefficient))) / 2));
    $perimeter += 2 * $correction;
    $correction = 2 * $outerRadius * pi() * 2 * (90 - (90 + qj(atan($coefficient))) / 2) / 360
        - 2 * $outerRadius * tan(wp(90 - (90 + qj(atan($coefficient))) / 2));
    $perimeter += 2 * $correction;
    $perimeter += $height + 1 * ($width - $wallThickness) * (1 / cos(atan($coefficient)) - $coefficient);
    $correction = 2 * $innerRadius * pi() * 2 * (90 - (90 + qj(atan($coefficient))) / 2) / 360
        - 2 * $innerRadius * tan(wp(90 - (90 + qj(atan($coefficient))) / 2));
    $perimeter += 2 * $correction;
    $correction = 2 * $outerRadius * pi() * 2 * (90 - (90 + qj(atan($coefficient))) / 2) / 360
        - 2 * $outerRadius * tan(wp(90 - (90 + qj(atan($coefficient))) / 2));
    $perimeter += 2 * $correction;

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
    $area += 4 * $correction;
    $correction = 0 - $outerRadius * $outerRadius / tan(wp((90 + qj(atan($coefficient))) / 2))
        + $outerRadius * $outerRadius * pi() * (90 - qj(atan($coefficient))) / 360;
    $area += 4 * $correction;

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

// формируем константы двутавра
extract(getDimensions($standard, $type));

$perimeter = getPerimeter($h, $b, $s, $R, $r ?? 0, $u ?? 0);// o
$sectionalArea = getSectionalArea($h, $b, $s, $t, $R, $r ?? 0, $u ?? 0);//t
$surfaceAreaPerMeter = getSurfaceAreaPerMeter($perimeter);//eu
$surfaceAreaPerTon = getSurfaceAreaPerTon($sectionalArea, $surfaceAreaPerMeter); //ppt

echo sprintf("Приведенная толщина металла: %.3f мм\n", $sectionalArea / $perimeter);
echo sprintf("Обогреваемый периметр: %.3f мм\n", $perimeter);
echo sprintf("Площадь поверхности / 1м: %.3f м2\n", $surfaceAreaPerMeter);
echo sprintf("Площадь поверхности / 1т: %.3f м2\n", $surfaceAreaPerTon);


exit();
