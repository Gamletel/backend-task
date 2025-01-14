# Ошибки
## GetCartController.php
* Некорректный выбор модификаторов доступа при объявлении переменных в конструкторе. Т.к. переменные не предназначены для использования в других классах, рекомендуется изменить модификатор доступа на private.
```
// Текущий код:
public function __construct(
    public CartView $cartView,
    public CartManager $cartManager
) {
}

// Рекомендуемая правка:
public function __construct(
    private CartView $cartView,
    private CartManager $cartManager
) {
}
```
* В методе get объявлена неиспользуемая переменная $request. Рекомендуется удалить, чтобы избежать лишнего когда.

## CartItem.php
* Некорректный выбор модификаторов доступа объявляемых переменных. Т.к. в классе есть геттеры, то рекомендуется изменить модификатор доступа переменных на private и readonly.
```
// Текущий код:
public function __construct(
        public string $uuid,
        public string $productUuid,
        public float $price,
        public int $quantity,
    ) {
    }

// Рекомендуемая правка:
public function __construct(
        readonly private string $uuid,
        readonly private string $productUuid,
        readonly private float $price,
        readonly private int $quantity,
    ) {
    }
```
## Customer.php
* В классе нет функционала по изменению данных пользователя, поэтому рекомендуется добавить модификатор доступа readonly для свойств конструктора, чтобы избежать переопределения.

## Connector.php
* Рекомендуется изменить объявление переменных конструктора на те, которые используются в большей части кода
* Ошибка при создании конструктора
    + Использован return, что нарушает назначение конструктора
```
public function __construct($redis)
    {
        return $this->redis = $redis;
    }
```
* Неверные вызов метода `$this->redis->get()`. Метод принимает string, а не Cart. Необходимо изменить передаваемую переменную.
```
// Исправленный код
return unserialize($this->redis->get($key));
```

## ConnectorExeption.php
* Рекомендуется добавить классу модификатор доступа readonly, для избежания возможных ошибок

* Рекомендуется добавить для свойства в конструкторе $previous проверку на null и указать значение по умолчанию (null), чтобы избежать возможных ошибок
```
// Текущий код
private ?\Throwable $previous,

// Рекомендуемая правка
private ?\Throwable $previous = null,
```
* Метод getPrevious, который реализуется из интерфейса Throwable, имеет возвращаемый тип Throwable. В классе необходимо убрать ? (проверку на null), чтобы соответствовать интерфейсу.
```
// Текущий код
public function getPrevious(): ?\Throwable
{
    return $this->previous;
}

// Исправленный
public function getPrevious(): \Throwable
{
    return $this->previous;
}
```

## ConnectorFacede.php
* Если нам необходим доступ к переменным лишь в дочерних классах, то стоит изменить модификатор доступа переменных класса на protected. Также, если нет необходимости переопределять переменные, то стоит добавить readonly для избежания возможных ошибок
```
// Текущий код
public string $host;
public int $port = 6379;
public ?string $password = null;
public ?int $dbindex = null;

//Исправленный
protected readonly string $host;
protected readonly int $port = 6379;
protected readonly ?string $password = null;
protected readonly ?int $dbindex = null;
```

* В методе build отсутствует обработка ошибок. Рекомендуется реализовать блок catch, например, как в коде ниже
```
protected function build(): void
{
    $redis = new Redis();
    try {
        $isConnected = $redis->connect($this->host, $this->port);
        if ($isConnected && $redis->ping('Pong')) {
            $redis->auth($this->password);
            $redis->select($this->dbindex);
            $this->connector = new Connector($redis);
        }
    } catch (RedisException $e) {
        error_log($e->getMessage());
    }
}
```

## CartManager.php
* В классе реализован сеттер, поэтому рекомендуется изменить модификатор доступа у переменной $logger на private и указать тип переменной (в нашем случае LoggerInterface)
```
// Исправленный код
private LoggerInterface $logger;
```

* В методе saveCart неверно вызван метод set. В методе требуется указать первым аргументов string, вторым - Cart.
```
//Исправленный код
try {
    $this->connector->set(session_id(), $cart);
} catch (Exception $e) {
    $this->logger->error('Error', $e);
}
```
* В методе getCart неверно вызван метод get. Метод принимает тип переменной Cart, а не string. Также неправильно создается экземпляр класса Cart (не хватает аргументов), чтобы исправить ошибку необходимо изменить конструктор класса Cart.
```
// Текущий код
public function getCart(Cart $cart)
{
    try {
        return $this->connector->get(session_id());
    } catch (Exception $e) {
        $this->logger->error('Error', $e);
    }

    return new Cart(session_id(), []);
}

//Исправленный
public function getCart(Cart $cart)
{
    try {
        return $this->connector->get($cart);
    } catch (Exception $e) {
        $this->logger->error('Error',);
    }

    return new Cart(session_id());
}
```
* Можно удалить неиспользуемый метод has

## ProductRepository.php
* В методах, где мы используем переменные в sql-запросах, необходимо использовать подготовленные запросы, чтобы обеспечить безопасность.
```
// Исправленный код
public function getByUuid(string $uuid): Product
{
    $row = $this->connection->fetchOne(
        "SELECT * FROM products WHERE uuid = ?",
        [$uuid]
    );

    if (empty($row)) {
        throw new Exception('Product not found');
    }

    return $this->make($row);
}
```
* В методе getByCategory в первом аргументе допущена ошибка: нельзя использовать $this->... внутри статической анонимной функции. Необходимо убрать static перед объявлением функции.
```
//Исправленный код
fn (array $row): Product => $this->make($row),
```

___

# Советы
* Придерживаться единого стиля кода. Пример:
```
// В большей части кода используется короткое объявление конструктора
public function __construct(
        private CartView $cartView,
        private CartManager $cartManager
    ) {
    }

// В некоторых классах используется другая стилистика
private Redis $redis;

public function __construct($redis)
{
    $this->redis = $redis;
}
```
* Объявлять корректные модификаторы доступа при создании классов и переменных в зависимости от использования.