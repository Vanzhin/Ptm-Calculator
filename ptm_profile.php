<?php
include 'standards_profile.php';
// на входе высота, ширина, толщина стенки в мм и стандарт профиля
$height = (int)$argv[1];
$width = (int)$argv[2];
$wallThickness = (float)$argv[3];
$standard = $argv[4] ?? null;
$isTop = $argv[5] === 'true';
$isBottom = $argv[6] === 'true';
$isLeft = $argv[7] === 'true';
$isRight = $argv[8] === 'true';

if (!((int)$isTop + (int)$isBottom + (int)$isLeft + (int)$isRight)) {
    exit("Профиль не обогревается.\n");
}

$availableStandards = [
    'ГОСТ_30245-2003', 'DIN_EN_10210-2-2006', 'DIN_EN_10219-2-2006'
];
if (!in_array($standard, $availableStandards)) {
    exit (sprintf("Стандарт %s не поддерживается, Выберите из (%s).\n", $standard, implode(', ', $availableStandards)));
}

function getPerimeter(
    int       $height,
    int       $width,
    int|float $wallThickness,
    string    $standard,
    bool      $isTop = true, bool $isBottom = true, bool $isLeft = true, bool $isRight = true,
): float
{
    $perimeter = 0;
    $radius = getRadius($standard, $height, $width, $wallThickness);
    if ($isTop) {
        $perimeter += $width + $radius * (0.5 * pi() - 2);
    }
    if ($isBottom) {
        $perimeter += $width + $radius * (0.5 * pi() - 2);
    }
    if ($isLeft) {
        $perimeter += $height + $radius * (0.5 * pi() - 2);
    }
    if ($isRight) {
        $perimeter += $height + $radius * (0.5 * pi() - 2);
    }

    return $perimeter;
}

function getSectionalArea(int $height, int $width, int|float $wallThickness, string $standard): float
{
    $radius = getRadius($standard, $height, $width, $wallThickness);
    $area = (2 * $width + 2 * $height - 4 * $wallThickness) * $wallThickness;
    $radiusArea = 0.25 * pi() * ($radius * $radius - ($radius - $wallThickness) * ($radius - $wallThickness)) - (2 * $radius - $wallThickness) * $wallThickness;

    return $area + 4 * $radiusArea;
}

function getSurfaceAreaPerMeter(float $perimeter): float
{
    return $perimeter / 1000;
}

function getSurfaceAreaPerTon(float $sectionalArea, float $surfaceAreaPerMeter): float
{
    return 1000 / ($sectionalArea / 1000 * 7.85) * $surfaceAreaPerMeter;
}

$perimeter = getPerimeter($height, $width, $wallThickness, $standard, $isTop, $isBottom, $isLeft, $isRight);// o
$sectionalArea = getSectionalArea($height, $width, $wallThickness, $standard);//t
$surfaceAreaPerMeter = getSurfaceAreaPerMeter($perimeter);
$surfaceAreaPerTon = getSurfaceAreaPerTon($sectionalArea, $surfaceAreaPerMeter);

echo sprintf("Приведенная толщина металла: %.3f мм\n", $sectionalArea / $perimeter);
echo sprintf("Обогреваемый периметр: %.3f мм\n", $perimeter);
echo sprintf("Площадь поверхности / 1м: %.3f м2\n", $surfaceAreaPerMeter);
echo sprintf("Площадь поверхности / 1т: %.3f м2\n", $surfaceAreaPerTon);


function getRadius(string $standard, int $height, int $width, int|float $wallThickness): int
{
    global $standardGost302245;
    $key = implode(' x ', [$height, $width, str_replace('.', ',', (string)($wallThickness))]);
    switch ($standard) {
        case 'ГОСТ_30245-2003':
            if (isset($standardGost302245[$key])) {
                return (int)$standardGost302245[$key]['R'] ?? 0;
            }
            exit(sprintf("Профиль с параметрами %s не найден.\n", $key));
//        case 'DIN_EN_10210_2_2006':
//        case 'DIN_EN_10219_2_2006':
//            return 0;
        default :
            return 0;
    }
}

exit();
