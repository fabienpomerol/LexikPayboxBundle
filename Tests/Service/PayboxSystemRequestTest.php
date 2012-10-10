<?php

namespace Lexik\Bundle\PayboxBundle\Tests\Service;

use Lexik\Bundle\PayboxBundle\Service\PayboxSystemRequest;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Paybox class tests.
 */
class PayboxSystemRequestTest extends \PHPUnit_Framework_TestCase
{
    private $_paybox;

    public function testInitParameters()
    {
        $this->assertEquals(1999888, $this->_paybox->getParameter('pbx_site'));
        $this->assertEquals(32, $this->_paybox->getParameter('pbx_rang'));
        $this->assertEquals(2, $this->_paybox->getParameter('pbx_identifiant'));
        $this->assertEquals('sha512', $this->_paybox->getParameter('pbx_hash'));
    }

    /**
     * @covers PayboxSystemRequest::getParameter
     * @covers PayboxSystemRequest::setParameter
     */
    public function testSetParameter()
    {
        $this->_paybox->setParameter('pbx_annule', 'Test');
        $this->_paybox->setParameter('PBX_ARCHIVAGE', 'Test');

        $this->assertEquals($this->_paybox->getParameter('PBX_ANNULE'), 'Test');
        $this->assertEquals($this->_paybox->getParameter('pbx_archivage'), 'Test');

        $this->assertNull($this->_paybox->getParameter('PBX_DISPLAY'));
    }

    /**
     * @covers PayboxSystemRequest::getParameter
     * @covers PayboxSystemRequest::setParameters
     */
    public function testSetParameters()
    {
        $this->_paybox->setParameters(array(
            'pbx_annule'    => 'Test',
            'PBX_ARCHIVAGE' => 'Test',
        ));

        $this->assertEquals($this->_paybox->getParameter('PBX_ANNULE'), 'Test');
        $this->assertEquals($this->_paybox->getParameter('pbx_archivage'), 'Test');
    }

    /**
     * @covers PayboxSystemRequest::getParameters
     * @covers PayboxSystemRequest::computeHmac
     * @covers PayboxSystemRequest::stringifyParameters
     */
    public function testGetParameters()
    {
        $this->assertTrue(null === $this->_paybox->getParameter('PBX_TIME'));
        $this->assertTrue(null === $this->_paybox->getParameter('PBX_HMAC'));

        $parameters = $this->_paybox->getParameters();

        $this->assertTrue(isset($parameters['PBX_TIME']));
        $this->assertTrue(isset($parameters['PBX_HMAC']));

        $qs  = 'PBX_CMD=cmd123';
        $qs .= '&PBX_DEVISE=978';
        $qs .= '&PBX_HASH=sha512';
        $qs .= '&PBX_IDENTIFIANT=2';
        $qs .= '&PBX_PORTEUR=test@test.net';
        $qs .= '&PBX_RANG=32';
        $qs .= '&PBX_RETOUR=Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K'; // "Sign:K" is automaticaly added at the end of PBX_RETOUR
        $qs .= '&PBX_SITE=1999888';
        $qs .= '&PBX_TIME='.$parameters['PBX_TIME'];
        $qs .= '&PBX_TOTAL=100';

        $this->assertEquals(
            $parameters['PBX_HMAC'],
            strtoupper(hash_hmac(
                'sha512',
                $qs,
                pack('H*', '0123456789ABCDEF')
            ))
        );
    }

    protected function setUp()
    {
        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->_paybox = new PayboxSystemRequest(array(
            'site'  => 1999888,
            'rank'  => 32,
            'login' => 2,
            'hmac'  => array(
                'algorithm' => 'sha512',
                'key'       => '0123456789ABCDEF',
            ),
        ), array(), $formFactory);

        $this->_paybox->setParameter('PBX_CMD',     'cmd123');
        $this->_paybox->setParameter('PBX_DEVISE',  '978');
        $this->_paybox->setParameter('PBX_PORTEUR', 'test@test.net');
        $this->_paybox->setParameter('PBX_RETOUR',  'Mt:M;Ref:R;Auto:A;Erreur:E');
        $this->_paybox->setParameter('PBX_TOTAL',   '100');
    }

    protected function tearDown()
    {
        $this->_paybox = null;
    }
}