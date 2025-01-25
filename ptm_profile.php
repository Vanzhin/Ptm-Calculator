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

    // счетчик сторон для добавления периметра при расчете
    $sideCount = 0;
    $adjacent = false;
    $radius = getRadius($standard, $height, $width, $wallThickness);

    //коэффициент для основного расчета
    $radCoef = 0.5;
    $addRadCoef = 0.5;
    if ($radius < 2 * $wallThickness) {
        $radCoef = 2.1458 / 4;
        $addRadCoef = 0.36445;
    }
    if ($radius > 2 * $wallThickness) {
        $radCoef = 2.00944 / 4;
        $addRadCoef = 0.491374;
    }

    if ($isTop) {
        $perimeter += $width + $radius * ($radCoef * pi() - 2);
        $sideCount++;
    }
    if ($isBottom) {
        $perimeter += $width + $radius * ($radCoef * pi() - 2);
        $sideCount++;
    }
    if ($isLeft) {
        $perimeter += $height + $radius * ($radCoef * pi() - 2);
        $sideCount++;
    }
    if ($isRight) {
        $perimeter += $height + $radius * ($radCoef * pi() - 2);
        $sideCount++;
    }
    if (!$isLeft && (!$isBottom || !$isTop)) {
        $adjacent = true;
    }
    if (!$isRight && (!$isBottom || !$isTop)) {
        $adjacent = true;
    }
    if (!$isTop && (!$isRight || !$isLeft)) {
        $adjacent = true;
    }
    if (!$isBottom && (!$isRight || !$isLeft)) {
        $adjacent = true;
    }
    if ($sideCount) {
        $perimeter += match (true) {
            $sideCount === 4 => 0,
            $sideCount === 3, $sideCount === 1, $sideCount === 2 && $adjacent === true => ($radius * $addRadCoef * pi()),
            $sideCount === 2 && $adjacent === false => 2 * ($radius * $addRadCoef * pi()),
        };
    }

    return $perimeter;
}

function getSectionalArea(int $height, int $width, int|float $wallThickness, string $standard): float
{
    $radius = getRadius($standard, $height, $width, $wallThickness);
    if (in_array($standard, ['DIN_EN_10210-2-2006', 'DIN_EN_10219-2-2006'])) {
        $innerRadius = getInnerRadius($standard, $wallThickness);
        return (2 * $wallThickness * ($height + $width - 2 * $wallThickness) - (4 - pi()) * (pow($radius, 2) - pow($innerRadius, 2)));
    }
    if ('ГОСТ_30245-2003' === $standard) {
        $area = (2 * $width + 2 * $height - 4 * $wallThickness) * $wallThickness;
        $radiusArea = 0.25 * pi() * ($radius * $radius - ($radius - $wallThickness) * ($radius - $wallThickness)) - (2 * $radius - $wallThickness) * $wallThickness;
        return $area + 4 * $radiusArea;
    }

    return 0;
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
echo sprintf("Площадь сечения: %.3f мм\n", $sectionalArea);
echo sprintf("Обогреваемый периметр: %.3f мм\n", $perimeter);
echo sprintf("Площадь поверхности / 1м: %.3f м2\n", $surfaceAreaPerMeter);
echo sprintf("Площадь поверхности / 1т: %.3f м2\n", $surfaceAreaPerTon);


function getRadius(string $standard, int $height, int $width, int|float $wallThickness): float
{
    global $standardGost302245;
    global $standardDin_EN_10219_2_2006;
    global $standardDin_EN_10210_2_2006;

    $key = implode(' x ', [$height, $width, str_replace('.', ',', (string)($wallThickness))]);
    switch ($standard) {
        case 'ГОСТ_30245-2003':
            if (isset($standardGost302245[$key])) {
                return (float)$standardGost302245[$key]['R'] ?? 0;
            }
            exit(sprintf("Профиль с параметрами %s не найден.\n", $key));
        case 'DIN_EN_10210-2-2006':
            //External corner radius (ro) for calculation is:
            //(ro = 1,5T)
            //(mm)
            if (isset($standardDin_EN_10210_2_2006[$key])) {
                return (float)$wallThickness * 1.5;
            }
            exit(sprintf("Профиль с параметрами %s не найден.\n", $key));

        case 'DIN_EN_10219-2-2006':
            //Внешний диаметр закругления ro в расчетах составляет:
            //- для толщины до 6 мм 2,0 Т (мм)
            //- для толщины от 6 до 10 мм 2,5 Т (мм)
            //- для толщины более 10 мм 3,0 Т (мм)
            if (isset($standardDin_EN_10219_2_2006[$key])) {
                return match (true) {
                    $wallThickness <= 6 => $wallThickness * 2,
                    $wallThickness > 6 && $wallThickness <= 10 => $wallThickness * 2.5,
                    $wallThickness > 10 => $wallThickness * 3,
                };
            }
            exit(sprintf("Профиль с параметрами %s не найден.\n", $key));
        default :
            return 0;
    }
}

function getInnerRadius(string $standard, int|float $wallThickness): float
{
    if ('DIN_EN_10219-2-2006' === $standard) {
        //Внутренний диаметр закругления ri в расчетах
        //составляет:
        //- для толщины до 6 мм 1,0 Т (мм)
        //- для толщины от 6 до 10 мм 1,5 Т (мм)
        //- для толщины более 10 мм 2,0 Т (мм)
        return match (true) {
            $wallThickness <= 6 => $wallThickness,
            $wallThickness > 6 && $wallThickness <= 10 => $wallThickness * 1.5,
            $wallThickness > 10 => $wallThickness * 2,
        };
    }
    if ('DIN_EN_10210-2-2006' === $standard) {
        return $wallThickness;
    }

    return 0;
}

exit();
