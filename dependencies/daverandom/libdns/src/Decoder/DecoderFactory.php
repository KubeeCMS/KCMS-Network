<?php

declare (strict_types=1);
/**
 * Creates Decoder objects
 *
 * PHP version 5.4
 *
 * @category LibDNS
 * @package Decoder
 * @author Chris Wright <https://github.com/DaveRandom>
 * @copyright Copyright (c) Chris Wright <https://github.com/DaveRandom>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @version 2.0.0
 */
namespace WP_Ultimo\Dependencies\LibDNS\Decoder;

use WP_Ultimo\Dependencies\LibDNS\Packets\PacketFactory;
use WP_Ultimo\Dependencies\LibDNS\Messages\MessageFactory;
use WP_Ultimo\Dependencies\LibDNS\Records\RecordCollectionFactory;
use WP_Ultimo\Dependencies\LibDNS\Records\QuestionFactory;
use WP_Ultimo\Dependencies\LibDNS\Records\ResourceBuilder;
use WP_Ultimo\Dependencies\LibDNS\Records\ResourceFactory;
use WP_Ultimo\Dependencies\LibDNS\Records\RDataBuilder;
use WP_Ultimo\Dependencies\LibDNS\Records\RDataFactory;
use WP_Ultimo\Dependencies\LibDNS\Records\Types\TypeBuilder;
use WP_Ultimo\Dependencies\LibDNS\Records\Types\TypeFactory;
use WP_Ultimo\Dependencies\LibDNS\Records\TypeDefinitions\TypeDefinitionManager;
use WP_Ultimo\Dependencies\LibDNS\Records\TypeDefinitions\TypeDefinitionFactory;
use WP_Ultimo\Dependencies\LibDNS\Records\TypeDefinitions\FieldDefinitionFactory;
/**
 * Creates Decoder objects
 *
 * @category LibDNS
 * @package Decoder
 * @author Chris Wright <https://github.com/DaveRandom>
 */
class DecoderFactory
{
    /**
     * Create a new Decoder object
     *
     * @param \LibDNS\Records\TypeDefinitions\TypeDefinitionManager $typeDefinitionManager
     * @param bool $allowTrailingData
     * @return Decoder
     */
    public function create(TypeDefinitionManager $typeDefinitionManager = null, bool $allowTrailingData = \true) : Decoder
    {
        $typeBuilder = new TypeBuilder(new TypeFactory());
        return new Decoder(new PacketFactory(), new MessageFactory(new RecordCollectionFactory()), new QuestionFactory(), new ResourceBuilder(new ResourceFactory(), new RDataBuilder(new RDataFactory(), $typeBuilder), $typeDefinitionManager ?: new TypeDefinitionManager(new TypeDefinitionFactory(), new FieldDefinitionFactory())), $typeBuilder, new DecodingContextFactory(), $allowTrailingData);
    }
}
