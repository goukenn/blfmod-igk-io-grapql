** igk/io/GraphQl
 
@C.A.D.BONDJEDOUE

The simple graphQL base QL implementation to get and retrieve data . 


```graphql
{
    basic
    first
    name
}
```
or 
```graphql
{basic, first, name}
```

calling listener 
```graphql
{
    userinfo(uid: 1){
        firstname
        lastname
    }    
}
```
### setting alias on caller function 
```graphql
{
    admin userinfo(uid: 1){
        firtname
        lastname
    }
    operator userinfo(usrOpType: 'operator'){

    }
}
```

#use in code 

```PHP
<?php
use igk\io\GraphQl\GraphQlParser;

igk_require_module(\igk\io\GraphQl::class);

$parse = GraphQlParser::Parse("{}", $data, $listener);

```