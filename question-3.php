<?php

class Address{
    private int $addrId;
    private string $details;
    private string $address;
    private float $lng;
    private float $lat;
    public function __construct( int $addrId, string $address, string $details, float $lng, float $lat )
    {
        $this->addrId = $addrId;
        $this->details = $details;
        $this->address = $address;
        $this->lng = $lng;
        $this->lat = $lat;
    }

    public function getId(){
        return $this->addrId;
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
        return  $this->weight;
    }
}

class ShippingAPI{

    public static function CalculateDeliveryCost( Address $destAddress, $weight ){

        $workZone = $destAddress->getWorkZone();
        // call external api
        // curl ...
        //

        return rand(5, 100);
    }
}

class Customer{

    private int $customerId;
    private string $name;
    private array $addresses;

    public function __construct(int $customerId, string $name )
    {
        $this->customerId = $customerId;
        $this->name = $name;
    }

    public function addAddress(Address $address){

         //address calculation or we can use a separate ArrayOfAddresses object
        $this->addresses[$address->getId()] = $address;
    }

    public function getId(){
        return $this->customerId;
    }

    /**
     * @description Customer Addresses
     * @return array
     */
    public function getAddress( $addrId ): array
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


class Cart
{
    private string $id;
    private Customer $customer;
    private Address $shipsToAddrId;

    public function __construct(Customer $customer, int $shipsToAddrId = 0)
    {
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
            $weight += $item->getDimensions()->getwWight();
        }

        $shippingRate = ShippingAPI::CalculateDeliveryCost( $this->getShippingqAddress(), $weight );
        return $amount + $shippingRate;
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
        $shippingRate = ShippingAPI::CalculateDeliveryCost( $this->getShippingqAddress(), $obj->getDimensions()->getwWight());
        return $obj->getCost() + $shippingRate;
    }

    public function setShippingqAddress($shipsToAddrId ){
        $this->shipsToAddrId = $shipsToAddrId;
    }

    public function getShippingqAddress(){
        return $this->customer->getAddress[$this->shipsToAddrId];
    }
}

class Item
{
    private int $productId;
    private string $name;
    private float $price;
    private int $taxRate;
    private array $dimensions;

    public function __construct(int $productId, string $name, float $price, array $dimensions=[], $taxRate=7)
    {
        $this->productId = $productId;
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

    private function getTaxRate(): int
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
}

$customer = new Customer(uniqid(), 'Mike Tyson');

$shipToAddrId = uniqid();
$address = new Address($shipToAddrId, '14833 Hillside Trl, Savage, MN, 55378', 47.651968, 9.478485);
$customer->addAddress($address);

$cart = new Cart($customer, $shipToAddrId);
$item = new Item(uniqid(), 'iPhone 14 Pro Max',  150.00, [150, 200, 20]);
$item2 = new Item(uniqid(), 'iPhone 13 Pro Max',  15.00, [150, 200, 20]);

$cart->addItem($item);
$cart->addItem($item2);

// Customer Name
$cart->getCustomer()->getName();

// Customer Addresses
$cart->getCustomer()->getAddresses();

// Items in Cart
$cart->getItems();

// Where Order Ships
$cart->getShippingqAddress();

// Cost of item in cart, including shipping and tax
$cart->getItemCost($item->getId());

// Total for all items
$cart->total();
