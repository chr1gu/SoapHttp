## Setup

```
git clone <repository>
curl -sS https://getcomposer.org/installer | php
./composer.phar install
```
Warning: the `cache` folder needs to be writeable for saving the WSDL file.

## Run Soap service

```
php -S 127.0.0.1:8888 -t web/
```

## Access WSDL

http://localhost:8888 **/index.php/wsdl**

Example Request Body
```xml
<?xml version="1.0" encoding="utf-8"?>
  <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <SoapHttpRequest xmlns="http://localhost:8888/index.php">
      <Url>http://fahrplan.sbb.ch/bin/query.exe/dn</Url>
      <Method>POST</Method>
      <Body>S=Bern&amp;Z=Luzern</Body>
      <Headers xsi:type="ns1:ArrayOfSTSHeader">
        <item xsi:type="ns1:STSHeader">
          <Key xsi:type="xsd:string">Content-Type</Key>
          <Value xsi:type="xsd:string">application/xml</Value>
        </item>
        <item xsi:type="ns1:STSHeader">
          <Key xsi:type="xsd:string">Referer</Key>
          <Value xsi:type="xsd:string">chrigu</Value>
        </item>
        <item xsi:type="ns1:STSHeader">
          <Key xsi:type="xsd:string">User-Agent:</Key>
          <Value xsi:type="xsd:string">LII-Cello/1.0 libwww/2.5</Value>
        </item>
      </Headers>
    </SoapHttpRequest>
  </soap:Body>
</soap:Envelope>
```

## Testing

Browse SoapClient examples:

[http://localhost:8888/index.php/test/get](http://localhost:8888/index.php/test/get)

[http://localhost:8888/index.php/test/post](http://localhost:8888/index.php/test/post)

Use Curl:

```
curl -X POST -d '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><SoapHttpRequest xmlns="http://localhost:8888/index.php"><Url>http://www.google.ch</Url><Method>GET</Method><Body></Body></SoapHttpRequest></soap:Body></soap:Envelope>' http://localhost:8888/index.php
```

```
curl -X POST -d '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><SoapHttpRequest xmlns="http://localhost:8888/index.php"><Url>http://fahrplan.sbb.ch/bin/query.exe/dn</Url><Method>POST</Method><Body>S=Bern&amp;Z=Luzern</Body><Headers xsi:type="ns1:ArrayOfSTSHeader"><item xsi:type="ns1:STSHeader"><Key xsi:type="xsd:string">Content-Type</Key><Value xsi:type="xsd:string">application/xml</Value></item><item xsi:type="ns1:STSHeader"><Key xsi:type="xsd:string">Referer</Key><Value xsi:type="xsd:string">chrigu</Value></item><item xsi:type="ns1:STSHeader"><Key xsi:type="xsd:string">User-Agent:</Key><Value xsi:type="xsd:string">LII-Cello/1.0 libwww/2.5</Value></item></Headers></SoapHttpRequest></soap:Body></soap:Envelope>' http://localhost:8888/index.php
```
