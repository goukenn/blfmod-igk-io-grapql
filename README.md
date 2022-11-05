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