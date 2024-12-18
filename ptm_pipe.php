<?php
// на входе внешний диаметр и толщина стенки в мм
$outerDiameter = (float)$argv[1];
$wallThickness = (float)$argv[2];
if ($wallThickness > $outerDiameter) {
    echo sprintf("Толщина стенки (%s мм) не может быть больше диаметра (%s мм).\n", $wallThickness, $outerDiameter);
    exit(1);
}

function getPipePerimeter(float $outerDiameter): float
{
    return $outerDiameter * pi();
}

function getPipeSectionalArea(float $outerDiameter, float $wallThickness): float
{
    return pi() / 4 * ($outerDiameter * $outerDiameter - ($outerDiameter - 2 * $wallThickness) * ($outerDiameter - 2 * $wallThickness));
}

function getSurfaceAreaPerMeter(float $perimeter): float
{
    return $perimeter / 1000;
}

function getSurfaceAreaPerTon(float $sectionalArea, float $surfaceAreaPerMeter): float
{
    return 1000 / ($sectionalArea / 1000 * 7.85) * $surfaceAreaPerMeter;
}

$perimeter = getPipePerimeter($outerDiameter);
$sectionalArea = getPipeSectionalArea($outerDiameter, $wallThickness);
$surfaceAreaPerMeter = getSurfaceAreaPerMeter($perimeter);
$surfaceAreaPerTon = getSurfaceAreaPerTon($sectionalArea, $surfaceAreaPerMeter);

echo sprintf("Приведенная толщина металла: %.3f мм\n", $sectionalArea/$perimeter);
echo sprintf("Обогреваемый периметр: %.3f мм\n", $perimeter);
echo sprintf("Площадь поверхности / 1м: %.3f м2\n", $surfaceAreaPerMeter);
echo sprintf("Площадь поверхности / 1т: %.3f м2\n", $surfaceAreaPerTon);

exit();







