<?php
/**
 * CreateMultiCollateralOrder
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
 * CreateMultiCollateralOrder Class Doc Comment
 *
 * @category Class
 * @package  GateApi
 * @author   GateIO
 * @link     https://www.gate.io
 */
class CreateMultiCollateralOrder implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'CreateMultiCollateralOrder';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'order_id' => 'string',
        'order_type' => 'string',
        'fixed_type' => 'string',
        'fixed_rate' => 'string',
        'auto_renew' => 'bool',
        'auto_repay' => 'bool',
        'borrow_currency' => 'string',
        'borrow_amount' => 'string',
        'collateral_currencies' => '\GateApi\Model\CollateralCurrency[]'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPIFormats = [
        'order_id' => null,
        'order_type' => null,
        'fixed_type' => null,
        'fixed_rate' => null,
        'auto_renew' => null,
        'auto_repay' => null,
        'borrow_currency' => null,
        'borrow_amount' => null,
        'collateral_currencies' => null
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
        'order_id' => 'order_id',
        'order_type' => 'order_type',
        'fixed_type' => 'fixed_type',
        'fixed_rate' => 'fixed_rate',
        'auto_renew' => 'auto_renew',
        'auto_repay' => 'auto_repay',
        'borrow_currency' => 'borrow_currency',
        'borrow_amount' => 'borrow_amount',
        'collateral_currencies' => 'collateral_currencies'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'order_id' => 'setOrderId',
        'order_type' => 'setOrderType',
        'fixed_type' => 'setFixedType',
        'fixed_rate' => 'setFixedRate',
        'auto_renew' => 'setAutoRenew',
        'auto_repay' => 'setAutoRepay',
        'borrow_currency' => 'setBorrowCurrency',
        'borrow_amount' => 'setBorrowAmount',
        'collateral_currencies' => 'setCollateralCurrencies'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'order_id' => 'getOrderId',
        'order_type' => 'getOrderType',
        'fixed_type' => 'getFixedType',
        'fixed_rate' => 'getFixedRate',
        'auto_renew' => 'getAutoRenew',
        'auto_repay' => 'getAutoRepay',
        'borrow_currency' => 'getBorrowCurrency',
        'borrow_amount' => 'getBorrowAmount',
        'collateral_currencies' => 'getCollateralCurrencies'
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
        $this->container['order_id'] = isset($data['order_id']) ? $data['order_id'] : null;
        $this->container['order_type'] = isset($data['order_type']) ? $data['order_type'] : null;
        $this->container['fixed_type'] = isset($data['fixed_type']) ? $data['fixed_type'] : null;
        $this->container['fixed_rate'] = isset($data['fixed_rate']) ? $data['fixed_rate'] : null;
        $this->container['auto_renew'] = isset($data['auto_renew']) ? $data['auto_renew'] : null;
        $this->container['auto_repay'] = isset($data['auto_repay']) ? $data['auto_repay'] : null;
        $this->container['borrow_currency'] = isset($data['borrow_currency']) ? $data['borrow_currency'] : null;
        $this->container['borrow_amount'] = isset($data['borrow_amount']) ? $data['borrow_amount'] : null;
        $this->container['collateral_currencies'] = isset($data['collateral_currencies']) ? $data['collateral_currencies'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['borrow_currency'] === null) {
            $invalidProperties[] = "'borrow_currency' can't be null";
        }
        if ($this->container['borrow_amount'] === null) {
            $invalidProperties[] = "'borrow_amount' can't be null";
        }
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
     * Gets order_id
     *
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->container['order_id'];
    }

    /**
     * Sets order_id
     *
     * @param string|null $order_id Order ID
     *
     * @return $this
     */
    public function setOrderId($order_id)
    {
        $this->container['order_id'] = $order_id;

        return $this;
    }

    /**
     * Gets order_type
     *
     * @return string|null
     */
    public function getOrderType()
    {
        return $this->container['order_type'];
    }

    /**
     * Sets order_type
     *
     * @param string|null $order_type current - current, fixed - fixed, if not specified, default to current
     *
     * @return $this
     */
    public function setOrderType($order_type)
    {
        $this->container['order_type'] = $order_type;

        return $this;
    }

    /**
     * Gets fixed_type
     *
     * @return string|null
     */
    public function getFixedType()
    {
        return $this->container['fixed_type'];
    }

    /**
     * Sets fixed_type
     *
     * @param string|null $fixed_type Fixed interest rate loan period: 7d - 7 days, 30d - 30 days. Must be provided for fixed
     *
     * @return $this
     */
    public function setFixedType($fixed_type)
    {
        $this->container['fixed_type'] = $fixed_type;

        return $this;
    }

    /**
     * Gets fixed_rate
     *
     * @return string|null
     */
    public function getFixedRate()
    {
        return $this->container['fixed_rate'];
    }

    /**
     * Sets fixed_rate
     *
     * @param string|null $fixed_rate Fixed interest rate, must be specified for fixed
     *
     * @return $this
     */
    public function setFixedRate($fixed_rate)
    {
        $this->container['fixed_rate'] = $fixed_rate;

        return $this;
    }

    /**
     * Gets auto_renew
     *
     * @return bool|null
     */
    public function getAutoRenew()
    {
        return $this->container['auto_renew'];
    }

    /**
     * Sets auto_renew
     *
     * @param bool|null $auto_renew Fixed interest rate, automatic renewal
     *
     * @return $this
     */
    public function setAutoRenew($auto_renew)
    {
        $this->container['auto_renew'] = $auto_renew;

        return $this;
    }

    /**
     * Gets auto_repay
     *
     * @return bool|null
     */
    public function getAutoRepay()
    {
        return $this->container['auto_repay'];
    }

    /**
     * Sets auto_repay
     *
     * @param bool|null $auto_repay Fixed interest rate, automatic repayment
     *
     * @return $this
     */
    public function setAutoRepay($auto_repay)
    {
        $this->container['auto_repay'] = $auto_repay;

        return $this;
    }

    /**
     * Gets borrow_currency
     *
     * @return string
     */
    public function getBorrowCurrency()
    {
        return $this->container['borrow_currency'];
    }

    /**
     * Sets borrow_currency
     *
     * @param string $borrow_currency Borrowed currency
     *
     * @return $this
     */
    public function setBorrowCurrency($borrow_currency)
    {
        $this->container['borrow_currency'] = $borrow_currency;

        return $this;
    }

    /**
     * Gets borrow_amount
     *
     * @return string
     */
    public function getBorrowAmount()
    {
        return $this->container['borrow_amount'];
    }

    /**
     * Sets borrow_amount
     *
     * @param string $borrow_amount Borrowing amount
     *
     * @return $this
     */
    public function setBorrowAmount($borrow_amount)
    {
        $this->container['borrow_amount'] = $borrow_amount;

        return $this;
    }

    /**
     * Gets collateral_currencies
     *
     * @return \GateApi\Model\CollateralCurrency[]|null
     */
    public function getCollateralCurrencies()
    {
        return $this->container['collateral_currencies'];
    }

    /**
     * Sets collateral_currencies
     *
     * @param \GateApi\Model\CollateralCurrency[]|null $collateral_currencies Collateral currency and amount
     *
     * @return $this
     */
    public function setCollateralCurrencies($collateral_currencies)
    {
        $this->container['collateral_currencies'] = $collateral_currencies;

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


