<?php

abstract class Model {
    private string $id;

    public function __construct()
    {
        $this->id = uniqid();
    }

    public function getId(){
        return $this->id;
    }
}
class Address extends Model{
    private string $details;
    private string $address;
    private float $lng;
    private float $lat;
    public function __construct(string $address, string $details, float $lng, float $lat )
    {
        parent::__construct();
        $this->details = $details;
        $this->address = $address;
        $this->lng = $lng;
        $this->lat = $lat;
    }

    /**
     * @return WorkZone|null
     */
    public function getWorkZone()
    {
        if (isset($this->lng, $this->lat)) {
            return WorkZone::findByEntry($this->lat . " " . $this->lng);
        } else {
            return null;
        }
    }

    public function getAddress()
    {
        return $this->address;
    }
}

class Package{

    private int $x;
    private int $y;
    private int $z;
    private int $weight;

    public function __construct( $x, $y, $z, $weight )
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->weight = $weight;
    }

    public function getWeight(){
        return $this->weight;
    }
}

class ShippingAPI{

    public static function CalculateDeliveryCost( Address $destAddress, $weight ){

        // $workZone = $destAddress->getWorkZone();
        // call external api
        // curl ...
        //

        return 14;
    }
}

class Customer extends Model{

    private string $name;
    private array $addresses;

    public function __construct(string $name )
    {
        parent::__construct();
        $this->name = $name;
    }

    public function addAddress(Address $address){

        //address calculation or we can use a separate ArrayOfAddresses object
        $this->addresses[$address->getId()] = $address;
    }

    /**
     * @description Customer Addresses
     * @param $addrId
     * @return Address
     */
    public function getAddress( $addrId ): Address
    {
        //address calculation or we can use a separate ArrayOfAddresses object
        return $this->addresses[$addrId];
    }

    /**
     * @description Customer Addresses
     * @return array
     */
    public function getAllAddresses(): array
    {
        //address calculation or we can use a separate ArrayOfAddresses object
        return $this->addresses;
    }

    /**
     * @description Customer Name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

}


class Cart extends Model
{
    private Customer $customer;
    private string $shipsToAddrId;

    public function __construct(Customer $customer, string $shipsToAddrId = '')
    {
        parent::__construct();
        $this->customer = $customer;
        $this->shipsToAddrId = $shipsToAddrId;
        $this->items = [];
    }

    public function getCustomer(){
        return $this->customer;
    }

    public function addItem( Item $item)
    {
        // we can add some calculation here
        $this->items[] = $item;
    }

    /**
     * @description Items in Cart
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @description total for all items
     * @return float
     */
    public function total()
    {
        $amount = 0.00;
        $items = $this->getItems();

        $weight = 0;
        foreach ($items as $item) {
            $amount += $item->getCost();
            $weight += $item->getDimensions()->getWeight();
        }

        $shippingRate = ShippingAPI::CalculateDeliveryCost( $this->getShippingqAddress(), $weight );
        return $amount + $shippingRate;
    }

    public function subTotal()
    {
        $amount = 0.00;
        $items = $this->getItems();
        foreach ($items as $item) {
            $amount += $item->getPrice();
        }
        return $amount;
    }

    public function calcTax()
    {
        $tax = 0.00;
        $items = $this->getItems();
        foreach ($items as $item) {
            $tax += floatval($item->getPrice() / 100 * $item->getTaxRate());
        }
        return $tax;
    }

    /**
     * @description Cost of item in cart, including shipping and tax
     * @return float
     */
    public function getItemCost($cartItemId): float
    {
        $obj = null;
        foreach( $this->items as $item){
            if($item->getId() == $cartItemId){
                $obj = $item;
                break;
            }
        }

        $shippingRate = ShippingAPI::CalculateDeliveryCost( $this->getShippingqAddress(), $obj->getDimensions()->getWeight());
        return $obj->getCost() + $shippingRate;
    }

    public function getShipping(){
        $items = $this->getItems();
        $weight = 0;
        foreach ($items as $item) {
            $weight += $item->getDimensions()->getWeight();
        }

        return ShippingAPI::CalculateDeliveryCost( $this->getShippingqAddress(), $weight );
    }

    public function setShippingqAddress($shipsToAddrId ){
        $this->shipsToAddrId = $shipsToAddrId;
    }

    public function getShippingqAddress(){
        return $this->customer->getAddress($this->shipsToAddrId);
    }
}

class Item extends Model
{
    private string $name;
    private float $price;
    private int $taxRate;
    private array $dimensions;

    public function __construct(string $name, float $price, array $dimensions=[], $taxRate=7)
    {
        parent::__construct();
        $this->name = $name;
        $this->price = $price;
        $this->dimensions = $dimensions;
        $this->taxRate = $taxRate;
    }

    public function getDimensions(){

        // we can pack an item to a package or
        // work with item dimensions to calculate how many items will fit for a box

        return new Package( ...$this->dimensions );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTaxRate(): int
    {
        return $this->taxRate;
    }

    /**
     * @description Cost of item in cart including tax
     * @return float
     */
    public function getCost(): float
    {
        $price = (float)($this->price + floatval($this->price / 100 * $this->getTaxRate()));
        return $price;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function display(){
        return $this->name . ': $' . $this->price;
    }
}

$customer = new Customer('Mike Tyson');
$address = new Address( '14833 Hillside Trl, Savage, MN, 55378','', 47.651968, 9.478485);
$customer->addAddress($address);
$cart = new Cart($customer, $address->getId());
$item = new Item( 'iPhone 14 Pro Max',  150.00, [150, 200, 20, 2]);
$item2 = new Item( 'iPhone 13 Pro Max',  15.00, [150, 200, 20, 2]);
$cart->addItem($item);
$cart->addItem($item2);

// console
/**
echo PHP_EOL;
echo '1. Customer Name'.PHP_EOL;
echo $cart->getCustomer()->getName();
echo PHP_EOL.PHP_EOL;

echo '2. Customer Addresses'.PHP_EOL;
print_r($cart->getCustomer()->getAllAddresses());
echo PHP_EOL.PHP_EOL;

echo '3. Items in Cart'.PHP_EOL;
print_r($cart->getItems());
echo PHP_EOL.PHP_EOL;

echo '4. Where Order Ships'.PHP_EOL;
print_r($cart->getShippingqAddress());
echo PHP_EOL.PHP_EOL;

echo '5. Cost of item in cart, including shipping and tax'.PHP_EOL;
echo '$'.$cart->getItemCost($item->getId());
echo PHP_EOL;
echo '$'.$cart->getItemCost($item2->getId());
echo PHP_EOL.PHP_EOL;

echo '6. Total for all items'.PHP_EOL;
echo '$'.$cart->total();
echo PHP_EOL.PHP_EOL;
 *
 *
 */
// web
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8"/>
    <title>Question 3</title>
</head>
<body style="padding: 15px; border: 1px solid #ccc;">

    <p><b>1. Customer Name</b></p>
    <p><?php echo $cart->getCustomer()->getName(); ?></p>
    <div>&nbsp;</div>

    <p><b>2. Customer Addresses</b></p>
    <?php foreach($cart->getCustomer()->getAllAddresses() as $addressItem): ?>
        <p><?php echo $addressItem->getAddress(); ?></p>
    <?php endforeach; ?>
    <div>&nbsp;</div>

    <p><b>3. Items in Cart</b></p>
    <?php foreach($cart->getItems() as $cartItem): ?>
        <p><?php echo $cartItem->display(); ?></p>
    <?php endforeach; ?>
    <div>&nbsp;</div>

    <p><b>4. Where Order Ships</b></p>
    <p><?php echo $cart->getShippingqAddress()->getAddress(); ?></p>
    <div>&nbsp;</div>

    <p><b>5. Cost of item in cart, including shipping and tax</b></p>
    <p>$<?php echo $cart->getItemCost($item->getId()); ?></p>
    <p>$<?php echo $cart->getItemCost($item2->getId()); ?></p>
    <div>&nbsp;</div>

    <p><b>6. Subtotal and total for all items</b></p>
    <p>Items Subtotal: $<?php echo $cart->subTotal(); ?></p>
    <p>Shipping: $<?php echo $cart->getShipping(); ?></p>
    <p>Total before tax: $<?php echo $cart->subTotal() + $cart->getShipping(); ?></p>
    <p>Estimated tax to be collected: $<?php echo $cart->calcTax(); ?></p>
    <p>Grand Total: $<?php echo $cart->total(); ?></p>
    <div>&nbsp;</div>

</body>
</html>
