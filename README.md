# filter_package
filtering product based on category, subcategory and manufacturer

How to use:

* load
```
require_once 'vendor/autoload.php'; 
use FilterPackage\Filter;
```

* initialize
```
$filter = new Filter(
    'mysql',
    'host',
    'databaseName',
    'username',
    'password'
);
```

* call the function
```
$filterList = $filter->getFilterFromQuery($query);
```
* the result will return as an array
