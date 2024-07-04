#!/usr/bin/env php

<?php

$data = [];

//print(file_get_contents("config.json"));

if (count($argv) == 1) {
    echo "\nFoydalanish: \nphp generate_crud.php --name=Name --path=Path --delete=true|false\n\n--name - CRUD nomi\n--path=CRUD url addresi\n--delete - true berilsa crud fayllari o'chib ketadi\n\n --path va --delete ixtiyoriy argumentlar\n\n";
    die();
}
foreach ($argv as $arg) {
    $option = explode('=', $arg);
    switch ($option[0]) {
        case '--name':
            $data['name'] = $option[1];
            break;
        case '--path':
            $data['path'] = $option[1];
            break;
        case '--delete':
            $data['delete'] = $option[1];
            break;
        case '--file':
            $data['json'] = json_decode(file_get_contents($option[1]), true);
            $data['fileName'] = $option[1];
            break;
    }
}

if (isset($data['json'])) {
    $migrations = [];
    $validations = [];
    $templates = [
        'relations' => [
            'belongsTo' => 'public function //name()
    {
        return $this->belongsTo(//parent::class);
    }',
            'hasMany' => 'public function //name()
    {
        return $this->hasMany(//parent::class);
    }',
            'hasOne' => 'public function //name()
    {
        return $this->hasOne(//parent::class);
    }'
        ],
        'translations' => 'public array $translatable = [//fields];',
        'with' => 'protected array $with = [//fields];',
        'casts' => 'protected $casts = [//fields];',
        'migration' => '$table->//type(//name);',
        'route' => "Route::apiResource('%path', %nameController::class);\n//routes",
        'import' => "%nameController,\n//imports"
    ];
    foreach ($data['json'] as $index => $model) {
        $attributes = '';
        if (!$model['created']) {
            if (isset($model['relations'])) {
                $model['imports'] = '';
                foreach ($model['relations'] as $relation) {
                    $model['imports'] .= "use App\\Models\\{$relation['parent']};\n";
                    $attributes .= str_replace(['//name', '//parent'], [$relation['name'], $relation['parent']], $templates['relations'][$relation['type']]) . "\n";
                }
            }
            if (isset($model['translatable'])) {
                $fields = '';
                foreach ($model['translatable'] as $translatable) {
                    $fields .= "'" . $translatable . '\', ';
                }
                $attributes .= str_replace('//fields', $fields, $templates['translations']) . "\n";
            }
            if (isset($model['with'])) {
                $fields = '';
                foreach ($model['with'] as $translatable) {
                    $fields .= "'" . $translatable . '\', ';
                }
                $attributes .= str_replace('//fields', $fields, $templates['with']) . "\n";
            }
            if (isset($model['casts'])) {
                $fields = '';
                foreach ($model['casts'] as $translatable) {
                    $fields .= $translatable;
                }
                $attributes .= str_replace('//fields', $fields, $templates['casts']) . "\n";
            }
            if (isset($model['fields'])) {
                foreach ($model['fields'] as $name => $type) {
                    $migrations[$model['name']][] = str_replace(['//name', '//type'], ["'$name'", explode(',', $type)[0]], $templates['migration']);
                    $validations[$model['name']][] = "\"$name\" => '" . explode(',', $type)[1] . "',";
                }
            }
            $data['attributes'] = $attributes;
            if(!make_crud($model, $data['attributes'], $migrations, $validations)) continue;

            $routeList = file_get_contents('routes/api.php');
            file_put_contents(
                'routes/api.php',
                str_replace(
                    ['//imports', '//routes'],
                    [
                        str_replace('%name', $model['name'], $templates['import']),
                        str_replace(
                            ['%name', '%path'],
                            [$model['name'], $model['path']],
                            $templates['route']
                        )
                    ],
                    $routeList
                )
            );
            $data['json'][$index]['created'] = true;
            $collection = str_replace(['%name', '%path'], [$model['name'], $model['path']], file_get_contents(__DIR__.'/templates/collection.json'));
            file_put_contents('collections/' . $model['name'] . '.json', $collection);
        }
    }

    file_put_contents($data['fileName'], json_encode($data['json']));
    die();
}

function make_crud($data, $attributes = '', $migrations = [], $validations = [])
{
    // 'app/Models/Product','templates/Product'
    $fileNames = ['app/Models/Product','app/Http/Requests/StoreProductRequest', 'app/Http/Requests/UpdateProductRequest', 'app/Http/Controllers/Api/ProductController', 'app/Repositories/ProductRepository', 'app/Services/ProductService'];

    $templateFiles = ['templates/Product','templates/StoreProductRequest', 'templates/UpdateProductRequest', 'templates/ProductController', 'templates/ProductRepository', 'templates/ProductService'];
    if (!isset($data['name'])) {
        echo "Iltimos model nomini  --name=Name ko'rinishida kiriting";
        die();
    }
    echo "Starting...\n";

    $data['path'] = $data['path'] ?? strtolower($data['name']);


    if ($data['delete']) {
        foreach ($fileNames as $fileName) {
            echo "O'chirilyapti: " . str_replace('Product', $data['name'], $fileName) . '.php' . "\n";
            !file_exists(str_replace('Product', $data['name'], $fileName) . '.php') || unlink(str_replace('Product', $data['name'], $fileName) . '.php');
        }
        return 0;
    }
    //
    foreach ($templateFiles as $key => $fileName) {
        echo "Generatsiya: " . str_replace('Product', $data['name'], $fileNames[$key]) . '.php' . "\n";
        $content = file_get_contents($fileName . '.php');
        print_r(array_values($validations[$data['name']]));
        $content = str_replace(['//attributes', '//imports', 'Product', 'products', 'product_id', '//rules'], [$attributes ?? '', $data['imports'] ?? '', $data['name'], $data['path'], strtolower($data['name']) . '_id', join("\n", $validations[$data['name']])], $content);
        file_put_contents(str_replace('Product', $data['name'], $fileNames[$key]) . '.php', $content);
    }

    echo "Migratsiya yaratilyapti..\n";

    foreach ($migrations as $key => $migration) {
        $migrations[$key] = join("\n", $migration);
    }
    $migration = str_replace(
        ['//table', '//fields'],
        [$data['path'], $migrations[$data['name']]],
        file_get_contents(__DIR__ . '/templates/migration.php')
    );
    file_put_contents(__DIR__ . '/database/migrations/' . date('Y_m_d_is') . "_create_" . (strtolower($data['name'])) . "s_table.php", $migration);
    return 1;
}

make_crud($data);
echo "Tugadi!\n";
////example: php generate_crud.php name=Museum
