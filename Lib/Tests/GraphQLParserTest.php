<?php
// @author: C.A.D. BONDJE DOUE
// @file: GraphQLParserTest.php
// @date: 20221105 09:30:53
namespace igk\io\GraphQl\Tests;

use igk\io\GraphQl\GraphQlParser;
use IGK\System\Exceptions\EnvironmentArrayException;
use IGK\Tests\Controllers\ModuleBaseTestCase;
use IGKException;

///<summary></summary>
/**
* 
* @package IGK
*/
class GraphQlParserTest extends ModuleBaseTestCase{
    var $controller;
    /**
     * retrieve the module 
     * @return mixed 
     * @throws IGKException 
     * @throws EnvironmentArrayException 
     */
    protected function getModule(){
        return igk_require_module(\igk\io\GraphQl::class);
    }
    public function test_parse_object(){
        $obj = GraphQlParser::Parse("{}");
        $this->assertIsObject($obj);
    }

    public function test_parse_object_2(){
        $obj = GraphQlParser::Parse("{name,firstname,lastname}");
        $this->assertEquals(
            json_encode((object)["name"=>null,"firstname"=>null, "lastname"=>null]),
            json_encode($obj)
        );
    }

    public function test_parse_object_3(){
        $obj = GraphQlParser::Parse("{name: String = 'charles' }");
        $this->assertEquals(
            json_encode((object)["name"=>'charles']),
            json_encode($obj)
        );
    }
    public function test_parse_object_4(){
        $obj = GraphQlParser::Parse(<<<'GQL'
{
    name
    firstname
    lastname
}
GQL
);
        $this->assertEquals(
            json_encode((object)["name"=>null,"firstname"=>null, "lastname"=>null]),
            json_encode($obj)
        );
    }

    public function test_parse_with_data(){
        $obj = GraphQlParser::Parse("{name,firstname,lastname}", [
            'firstname'=>'C.A.D',
            'lastname'=>'BONDJE DOUE',
        ]);
        $this->assertEquals(
            json_encode((object)["name"=>null,"firstname"=>'C.A.D', "lastname"=>'BONDJE DOUE']),
            json_encode($obj)
        );
    }
    public function test_parse_with_data_2(){
 
        $obj = GraphQlParser::Parse("{name,firstname,lastname,age:Int}", [
            'firstname'=>'C.A.D',
            'lastname'=>'BONDJE DOUE',
        ]);
        $this->assertEquals(
            json_encode((object)["name"=>null,"firstname"=>'C.A.D', "lastname"=>'BONDJE DOUE', 'age'=>0]),
            json_encode($obj)
        );
    }

    public function test_parse_listener(){
         
        $obj = GraphQlParser::Parse("{user (id:1){ name } lastname }", [
            'firstname'=>'C.A.D',
            'lastname'=>'BONDJE DOUE',
        ], new MockGraphListener);
        $this->assertEquals(
            json_encode((object)["user"=>["name"=>"user1"], "lastname"=>'BONDJE DOUE']),
            json_encode($obj)
        );
    }

    public function test_parse_with_alias_listener(){
         
        $obj = GraphQlParser::Parse("{localuser   user (id:1){ name } lastname }", [
            'firstname'=>'C.A.D',
            'lastname'=>'BONDJE DOUE',
        ], new MockGraphListener);
        $this->assertEquals(
            json_encode((object)["localuser"=>["name"=>"user1"], "lastname"=>'BONDJE DOUE']),
            json_encode($obj)
        );
    }
}