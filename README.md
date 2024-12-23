# Проект "Калькулятор ПТМ"

Содержит простые файлы для расчета птм нескольких видов профилей

Пример расчетов на https://lpz.su/info/kalkulyator-ptm/

## Как пользоваться

1. В консоли исполняем необходимый файл путем выбора по названию, например, для двутавра это ptm_i_beam.php с нужными
   параметрами
2. У каждого профиля свои входные параметры, можно посмотреть в начале файла
   ```shell
   // на входе стандарт двутавра и марку двутавра
   $standard = $argv[1];
   $type = $argv[2];
   ```
3. Пример выполнения команды для двутавра

   ```shell
   php ptm_i_beam.php СТО_АСЧМ_20-93 30Ш2 true true true true
   ```
4. Пример выполнения команды для уголка

   ```shell
    php ptm_angle.php 250 250 35 ГОСТ_8509-93 true true
   ```
5. Пример выполнения команды для швеллера

   ```shell
    php ptm_channel.php ГОСТ_8240-97 5У true tru true true
   ```
6. Пример выполнения команды для двутавра по размерам

   ```shell
   php ptm_i_beam_custom.php 20 2 30 3 true true true true
   ```
7. Пример выполнения команды для трубы

   ```shell
   php ptm_pipe.php 50 4
   ```
8. Пример выполнения команды для профиля

   ```shell
   php ptm_profile.php 40 40 2.5 ГОСТ_30245-2003 true true true true
   ```

9. Пример выполнения команды для листа

   ```shell
   php ptm_sheet.php 12 100
   ```

10. Пример вывода расчета в консоли
    ```shell
    Приведенная толщина металла: 4.571 мм
    Обогреваемый периметр: 1152.681 мм
    Площадь поверхности / 1м: 1.153 м2
    Площадь поверхности / 1т: 27.868 м2
    ```
