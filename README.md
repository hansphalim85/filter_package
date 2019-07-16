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
$query = "SELECT 
  p.prdid,
  p.model,
  c.catid,
  c.catname,
  s.subid,
  s.subname,
  m.manid,
  m.manname 
FROM
  product p 
  LEFT JOIN category c 
    ON c.catid = p.catid 
  LEFT JOIN subcategory s 
    ON s.subid = p.subid 
  LEFT JOIN manufacturer m 
    ON m.manid = p.manid";
    
$filterList = $filter->getFilterFromQuery($query);
```
or manually build when the query is too heavy
```
$filterList = $filter->getFilterManually($query);
```

* the result will return as an array
```
array (size=2)
  'cat_subcat' => 
    array (size=3)
      4 => 
        array (size=3)
          'catname' => string 'Cases' (length=5)
          37 => 
            array (size=2)
              ...
          'count' => string '2' (length=1)
      16 => 
        array (size=4)
          'catname' => string 'Memory' (length=6)
          44 => 
            array (size=2)
              ...
          42 => 
            array (size=2)
              ...
          'count' => int 2
      18 => 
        array (size=4)
          'catname' => string 'Monitors' (length=8)
          5 => 
            array (size=2)
              ...
          6 => 
            array (size=2)
              ...
          'count' => int 6
  'manufacturer' => 
    array (size=7)
      14 => 
        array (size=2)
          'manname' => string 'AOC' (length=3)
          'count' => string '3' (length=1)
      13 => 
        array (size=2)
          'manname' => string 'AOpen' (length=5)
          'count' => string '2' (length=1)
      66 => 
        array (size=2)
          'manname' => string 'Hynix Semiconductor' (length=19)
          'count' => string '1' (length=1)
      20 => 
        array (size=2)
          'manname' => string 'LG' (length=2)
          'count' => string '1' (length=1)
      22 => 
        array (size=2)
          'manname' => string 'Samsung' (length=7)
          'count' => string '1' (length=1)
      9 => 
        array (size=2)
          'manname' => string 'SONY' (length=4)
          'count' => string '1' (length=1)
      74 => 
        array (size=2)
          'manname' => string 'Viewmaster' (length=10)
          'count' => string '1' (length=1)
```
