** igk/io/GraphQl
 
@C.A.D.BONDJEDOUE

The simple graphQL base QL implementation to gget and retrieve data . 


```
{
    basic
    first
    name
}
```
or 
```
{basic, first, name}
```

calling listener 

{
    userinfo(uid: 1){
        firstname
        lastname
    }    
}

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