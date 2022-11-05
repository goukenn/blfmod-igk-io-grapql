<?php
// @author: C.A.D. BONDJE DOUE
// @file: MockGraphListener.php
// @date: 20221105 17:47:07
namespace igk\io\GraphQl\Tests;


///<summary></summary>
/**
* 
* @package IGK
*/
class MockGraphListener{
    public function user(){ 
        return [
            "name"=>"user1",
        ];
    }
    public function picture(){ 
        return [
            "url"=>"https://com.test.balafon.get-picure/",
        ];
    }
}