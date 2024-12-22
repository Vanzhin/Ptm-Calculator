<?php
// на входе ширина полки, толщина полки, ширина стенки, толщина стенки в мм
$shelfWidth = (float)$argv[1];
$shelfThickness = (float)$argv[2];
$wallWidth = (float)$argv[3];
$wallThickness = (float)$argv[4];
$isTop = $argv[5] === 'true';
$isBottom = $argv[6] === 'true';
$isLeft = $argv[7] === 'true';
$isRight = $argv[8] === 'true';

if (!((int)$isTop + (int)$isBottom + (int)$isLeft + (int)$isRight)) {
    exit("Двутавр не обогревается.\n");
}
if ($shelfThickness > $shelfWidth) {
    echo sprintf("Толщина полки (%s мм) не может быть больше ширины (%s мм).\n", $shelfThickness, $shelfWidth);
    exit(1);
}
if ($wallThickness > $wallWidth) {
    echo sprintf("Толщина стенки (%s мм) не может быть больше ширины (%s мм).\n", $wallThickness, $wallWidth);
    exit(1);
}

function getPerimeter(
    float $shelfWidth,
    float $shelfThickness,
    float $wallWidth,
    float $wallThickness,
    bool  $isTop = true, bool $isBottom = true, bool $isLeft = true, bool $isRight = true
): float
{
    $perimeter = 0;
    if ($isTop) {
        $perimeter += $shelfWidth;
    }
    if ($isBottom) {
        $perimeter += $shelfWidth;
    }
    if ($isLeft) {
        $perimeter += $wallWidth + 2 * $shelfThickness + $shelfWidth - $wallThickness;
    }
    if ($isRight) {
        $perimeter += $wallWidth + 2 * $shelfThickness + $shelfWidth - $wallThickness;
    }

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

$perimeter = getPerimeter($shelfWidth, $shelfThickness, $wallWidth, $wallThickness, $isTop, $isBottom, $isLeft, $isRight);
$sectionalArea = getSectionalArea($shelfWidth, $shelfThickness, $wallWidth, $wallThickness);
$surfaceAreaPerMeter = getSurfaceAreaPerMeter($perimeter);
$surfaceAreaPerTon = getSurfaceAreaPerTon($sectionalArea, $surfaceAreaPerMeter);

echo sprintf("Приведенная толщина металла: %.3f мм\n", $sectionalArea / $perimeter);
echo sprintf("Обогреваемый периметр: %.3f мм\n", $perimeter);
echo sprintf("Площадь поверхности / 1м: %.3f м2\n", $surfaceAreaPerMeter);
echo sprintf("Площадь поверхности / 1т: %.3f м2\n", $surfaceAreaPerTon);

exit();
