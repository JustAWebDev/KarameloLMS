<?php

class HTMLPurifier_ConfigSchema_InterchangeTest extends UnitTestCase
{
    
    protected $interchange;
    
    public function setup() {
        $this->interchange = new HTMLPurifier_ConfigSchema_Interchange();
    }
    
    function testAddNamespace() {
        $v = new HTMLPurifier_ConfigSchema_Interchange_Namespace();
        $v->namespace = 'Namespace';
        $this->interchange->addNamespace($v);
        $this->assertIdentical($v, $this->interchange->namespaces['Namespace']);
    }
    
    function testAddDirective() {
        $v = new HTMLPurifier_ConfigSchema_Interchange_Directive();
        $v->id = new HTMLPurifier_ConfigSchema_Interchange_Id('Namespace', 'Directive');
        $this->interchange->addDirective($v);
        $this->assertIdentical($v, $this->interchange->directives['Namespace.Directive']);
    }
    
}
