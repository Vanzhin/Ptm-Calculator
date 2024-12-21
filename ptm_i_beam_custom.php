<?php
// на входе ширина полки, толщина полки, ширина стенки, толщина стенки в мм
$shelfWidth = (float)$argv[1];
$shelfThickness = (float)$argv[2];
$wallWidth = (float)$argv[3];
$wallThickness = (float)$argv[4];

if ($shelfThickness > $shelfWidth) {
    echo sprintf("Толщина полки (%s мм) не может быть больше ширины (%s мм).\n", $shelfThickness, $shelfWidth);
    exit(1);
}
if ($wallWidth > $wallThickness) {
    echo sprintf("Толщина стенки (%s мм) не может быть больше ширины (%s мм).\n", $wallThickness, $wallThickness);
    exit(1);
}

function getPerimeter(float $shelfWidth, float $shelfThickness, float $wallWidth, float $wallThickness): float
{
    $perimeter = 0;
    $perimeter += $shelfWidth;
    $perimeter += $shelfWidth;
    $perimeter += $wallWidth + 2 * $shelfThickness + $shelfWidth - $wallThickness;
    $perimeter += $wallWidth + 2 * $shelfThickness + $shelfWidth - $wallThickness;

    return $perimeter;
}

function getSectionalArea(float $shelfWidth, float $shelfThickness, float $wallWidth, float $wallThickness): float
{
    return $shelfWidth * $shelfThickness * 2 + ($wallWidth + 2 * $shelfThickness - $shelfThickness - $shelfThickness) * $wallThickness;
}

function getSurfaceAreaPerMeter(float $perimeter): float
{
    return $perimeter / 1000;
}

function getSurfaceAreaPerTon(float $sectionalArea, float $surfaceAreaPerMeter): float
{
    return 1000 / ($sectionalArea / 1000 * 7.85) * $surfaceAreaPerMeter;
}

$perimeter = getPerimeter($shelfWidth, $shelfThickness, $wallWidth, $wallThickness);
$sectionalArea = getSectionalArea($shelfWidth, $shelfThickness, $wallWidth, $wallThickness);
$surfaceAreaPerMeter = getSurfaceAreaPerMeter($perimeter);
$surfaceAreaPerTon = getSurfaceAreaPerTon($sectionalArea, $surfaceAreaPerMeter);

echo sprintf("Приведенная толщина металла: %.3f мм\n", $sectionalArea / $perimeter);
echo sprintf("Обогреваемый периметр: %.3f мм\n", $perimeter);
echo sprintf("Площадь поверхности / 1м: %.3f м2\n", $surfaceAreaPerMeter);
echo sprintf("Площадь поверхности / 1т: %.3f м2\n", $surfaceAreaPerTon);

exit();
