<?php
/**
 * CrossMarginCurrency
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
 * CrossMarginCurrency Class Doc Comment
 *
 * @category Class
 * @package  GateApi
 * @author   GateIO
 * @link     https://www.gate.io
 */
class CrossMarginCurrency implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'CrossMarginCurrency';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'name' => 'string',
        'rate' => 'string',
        'prec' => 'string',
        'discount' => 'string',
        'min_borrow_amount' => 'string',
        'user_max_borrow_amount' => 'string',
        'total_max_borrow_amount' => 'string',
        'price' => 'string',
        'loanable' => 'bool',
        'status' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPIFormats = [
        'name' => null,
        'rate' => null,
        'prec' => null,
        'discount' => null,
        'min_borrow_amount' => null,
        'user_max_borrow_amount' => null,
        'total_max_borrow_amount' => null,
        'price' => null,
        'loanable' => null,
        'status' => null
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
        'name' => 'name',
        'rate' => 'rate',
        'prec' => 'prec',
        'discount' => 'discount',
        'min_borrow_amount' => 'min_borrow_amount',
        'user_max_borrow_amount' => 'user_max_borrow_amount',
        'total_max_borrow_amount' => 'total_max_borrow_amount',
        'price' => 'price',
        'loanable' => 'loanable',
        'status' => 'status'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'name' => 'setName',
        'rate' => 'setRate',
        'prec' => 'setPrec',
        'discount' => 'setDiscount',
        'min_borrow_amount' => 'setMinBorrowAmount',
        'user_max_borrow_amount' => 'setUserMaxBorrowAmount',
        'total_max_borrow_amount' => 'setTotalMaxBorrowAmount',
        'price' => 'setPrice',
        'loanable' => 'setLoanable',
        'status' => 'setStatus'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'name' => 'getName',
        'rate' => 'getRate',
        'prec' => 'getPrec',
        'discount' => 'getDiscount',
        'min_borrow_amount' => 'getMinBorrowAmount',
        'user_max_borrow_amount' => 'getUserMaxBorrowAmount',
        'total_max_borrow_amount' => 'getTotalMaxBorrowAmount',
        'price' => 'getPrice',
        'loanable' => 'getLoanable',
        'status' => 'getStatus'
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
        $this->container['name'] = isset($data['name']) ? $data['name'] : null;
        $this->container['rate'] = isset($data['rate']) ? $data['rate'] : null;
        $this->container['prec'] = isset($data['prec']) ? $data['prec'] : null;
        $this->container['discount'] = isset($data['discount']) ? $data['discount'] : null;
        $this->container['min_borrow_amount'] = isset($data['min_borrow_amount']) ? $data['min_borrow_amount'] : null;
        $this->container['user_max_borrow_amount'] = isset($data['user_max_borrow_amount']) ? $data['user_max_borrow_amount'] : null;
        $this->container['total_max_borrow_amount'] = isset($data['total_max_borrow_amount']) ? $data['total_max_borrow_amount'] : null;
        $this->container['price'] = isset($data['price']) ? $data['price'] : null;
        $this->container['loanable'] = isset($data['loanable']) ? $data['loanable'] : null;
        $this->container['status'] = isset($data['status']) ? $data['status'] : null;
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
     * Gets name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->container['name'];
    }

    /**
     * Sets name
     *
     * @param string|null $name Currency name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->container['name'] = $name;

        return $this;
    }

    /**
     * Gets rate
     *
     * @return string|null
     */
    public function getRate()
    {
        return $this->container['rate'];
    }

    /**
     * Sets rate
     *
     * @param string|null $rate Minimum lending rate (hourly rate)
     *
     * @return $this
     */
    public function setRate($rate)
    {
        $this->container['rate'] = $rate;

        return $this;
    }

    /**
     * Gets prec
     *
     * @return string|null
     */
    public function getPrec()
    {
        return $this->container['prec'];
    }

    /**
     * Sets prec
     *
     * @param string|null $prec Currency precision
     *
     * @return $this
     */
    public function setPrec($prec)
    {
        $this->container['prec'] = $prec;

        return $this;
    }

    /**
     * Gets discount
     *
     * @return string|null
     */
    public function getDiscount()
    {
        return $this->container['discount'];
    }

    /**
     * Sets discount
     *
     * @param string|null $discount Currency value discount, which is used in total value calculation
     *
     * @return $this
     */
    public function setDiscount($discount)
    {
        $this->container['discount'] = $discount;

        return $this;
    }

    /**
     * Gets min_borrow_amount
     *
     * @return string|null
     */
    public function getMinBorrowAmount()
    {
        return $this->container['min_borrow_amount'];
    }

    /**
     * Sets min_borrow_amount
     *
     * @param string|null $min_borrow_amount Minimum currency borrow amount. Unit is currency itself
     *
     * @return $this
     */
    public function setMinBorrowAmount($min_borrow_amount)
    {
        $this->container['min_borrow_amount'] = $min_borrow_amount;

        return $this;
    }

    /**
     * Gets user_max_borrow_amount
     *
     * @return string|null
     */
    public function getUserMaxBorrowAmount()
    {
        return $this->container['user_max_borrow_amount'];
    }

    /**
     * Sets user_max_borrow_amount
     *
     * @param string|null $user_max_borrow_amount Maximum borrow value allowed per user, in USDT
     *
     * @return $this
     */
    public function setUserMaxBorrowAmount($user_max_borrow_amount)
    {
        $this->container['user_max_borrow_amount'] = $user_max_borrow_amount;

        return $this;
    }

    /**
     * Gets total_max_borrow_amount
     *
     * @return string|null
     */
    public function getTotalMaxBorrowAmount()
    {
        return $this->container['total_max_borrow_amount'];
    }

    /**
     * Sets total_max_borrow_amount
     *
     * @param string|null $total_max_borrow_amount Maximum borrow value allowed for this currency, in USDT
     *
     * @return $this
     */
    public function setTotalMaxBorrowAmount($total_max_borrow_amount)
    {
        $this->container['total_max_borrow_amount'] = $total_max_borrow_amount;

        return $this;
    }

    /**
     * Gets price
     *
     * @return string|null
     */
    public function getPrice()
    {
        return $this->container['price'];
    }

    /**
     * Sets price
     *
     * @param string|null $price Price change between this currency and USDT
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->container['price'] = $price;

        return $this;
    }

    /**
     * Gets loanable
     *
     * @return bool|null
     */
    public function getLoanable()
    {
        return $this->container['loanable'];
    }

    /**
     * Sets loanable
     *
     * @param bool|null $loanable Whether currency is borrowed
     *
     * @return $this
     */
    public function setLoanable($loanable)
    {
        $this->container['loanable'] = $loanable;

        return $this;
    }

    /**
     * Gets status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->container['status'];
    }

    /**
     * Sets status
     *
     * @param int|null $status status  - `0` : disable  - `1` : enable
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->container['status'] = $status;

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


