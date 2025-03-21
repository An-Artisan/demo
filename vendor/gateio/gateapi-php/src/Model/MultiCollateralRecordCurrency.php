<?php
/**
 * MultiCollateralRecordCurrency
 *
 * PHP version 7
 *
 * @category Class
 * @package  GateApi
 * @author   GateIO
 * @link     https://www.gate.io
 */

/**
 * Gate API v4
 *
 * Welcome to Gate.io API  APIv4 provides spot, margin and futures trading operations. There are public APIs to retrieve the real-time market statistics, and private APIs which needs authentication to trade on user's behalf.
 *
 * Contact: support@mail.gate.io
 * Generated by: https://openapi-generator.tech
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * Do not edit the class manually.
 */

namespace GateApi\Model;

use \ArrayAccess;
use \GateApi\ObjectSerializer;

/**
 * MultiCollateralRecordCurrency Class Doc Comment
 *
 * @category Class
 * @package  GateApi
 * @author   GateIO
 * @link     https://www.gate.io
 */
class MultiCollateralRecordCurrency implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'MultiCollateralRecordCurrency';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'currency' => 'string',
        'index_price' => 'string',
        'before_amount' => 'string',
        'before_amount_usdt' => 'string',
        'after_amount' => 'string',
        'after_amount_usdt' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPIFormats = [
        'currency' => null,
        'index_price' => null,
        'before_amount' => null,
        'before_amount_usdt' => null,
        'after_amount' => null,
        'after_amount_usdt' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'currency' => 'currency',
        'index_price' => 'index_price',
        'before_amount' => 'before_amount',
        'before_amount_usdt' => 'before_amount_usdt',
        'after_amount' => 'after_amount',
        'after_amount_usdt' => 'after_amount_usdt'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'currency' => 'setCurrency',
        'index_price' => 'setIndexPrice',
        'before_amount' => 'setBeforeAmount',
        'before_amount_usdt' => 'setBeforeAmountUsdt',
        'after_amount' => 'setAfterAmount',
        'after_amount_usdt' => 'setAfterAmountUsdt'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'currency' => 'getCurrency',
        'index_price' => 'getIndexPrice',
        'before_amount' => 'getBeforeAmount',
        'before_amount_usdt' => 'getBeforeAmountUsdt',
        'after_amount' => 'getAfterAmount',
        'after_amount_usdt' => 'getAfterAmountUsdt'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }

    

    

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['currency'] = isset($data['currency']) ? $data['currency'] : null;
        $this->container['index_price'] = isset($data['index_price']) ? $data['index_price'] : null;
        $this->container['before_amount'] = isset($data['before_amount']) ? $data['before_amount'] : null;
        $this->container['before_amount_usdt'] = isset($data['before_amount_usdt']) ? $data['before_amount_usdt'] : null;
        $this->container['after_amount'] = isset($data['after_amount']) ? $data['after_amount'] : null;
        $this->container['after_amount_usdt'] = isset($data['after_amount_usdt']) ? $data['after_amount_usdt'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets currency
     *
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->container['currency'];
    }

    /**
     * Sets currency
     *
     * @param string|null $currency Currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->container['currency'] = $currency;

        return $this;
    }

    /**
     * Gets index_price
     *
     * @return string|null
     */
    public function getIndexPrice()
    {
        return $this->container['index_price'];
    }

    /**
     * Sets index_price
     *
     * @param string|null $index_price Currency Index Price
     *
     * @return $this
     */
    public function setIndexPrice($index_price)
    {
        $this->container['index_price'] = $index_price;

        return $this;
    }

    /**
     * Gets before_amount
     *
     * @return string|null
     */
    public function getBeforeAmount()
    {
        return $this->container['before_amount'];
    }

    /**
     * Sets before_amount
     *
     * @param string|null $before_amount Amount before the operation
     *
     * @return $this
     */
    public function setBeforeAmount($before_amount)
    {
        $this->container['before_amount'] = $before_amount;

        return $this;
    }

    /**
     * Gets before_amount_usdt
     *
     * @return string|null
     */
    public function getBeforeAmountUsdt()
    {
        return $this->container['before_amount_usdt'];
    }

    /**
     * Sets before_amount_usdt
     *
     * @param string|null $before_amount_usdt USDT Amount before the operation.
     *
     * @return $this
     */
    public function setBeforeAmountUsdt($before_amount_usdt)
    {
        $this->container['before_amount_usdt'] = $before_amount_usdt;

        return $this;
    }

    /**
     * Gets after_amount
     *
     * @return string|null
     */
    public function getAfterAmount()
    {
        return $this->container['after_amount'];
    }

    /**
     * Sets after_amount
     *
     * @param string|null $after_amount Amount after the operation.
     *
     * @return $this
     */
    public function setAfterAmount($after_amount)
    {
        $this->container['after_amount'] = $after_amount;

        return $this;
    }

    /**
     * Gets after_amount_usdt
     *
     * @return string|null
     */
    public function getAfterAmountUsdt()
    {
        return $this->container['after_amount_usdt'];
    }

    /**
     * Sets after_amount_usdt
     *
     * @param string|null $after_amount_usdt USDT Amount after the operation.
     *
     * @return $this
     */
    public function setAfterAmountUsdt($after_amount_usdt)
    {
        $this->container['after_amount_usdt'] = $after_amount_usdt;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


