<?php
/**
 * PayPal Service Class
 * Handles PayPal API interactions
 */

require_once __DIR__ . '/paypal_config.php';

class PayPalService {
    private $credentials;
    private $accessToken;
    
    public function __construct() {
        $this->credentials = PayPalConfig::getCredentials();
    }
    
    /**
     * Get PayPal access token
     */
    private function getAccessToken() {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->credentials['base_url'] . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->credentials['client_id'] . ':' . $this->credentials['client_secret']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Accept-Language: en_US'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $this->accessToken = $data['access_token'];
            return $this->accessToken;
        }
        
        throw new Exception('Failed to get PayPal access token');
    }
    
    /**
     * Create PayPal order
     */
    public function createOrder($orderData) {
        $accessToken = $this->getAccessToken();
        $redirectUrls = PayPalConfig::getRedirectUrls();
        
        // Use provided values or calculate if not provided
        $itemTotal = $orderData['subtotal'] ?? 0;
        $shippingCost = $orderData['shipping_fee'] ?? 100.00;
        $discountAmount = $orderData['discount_amount'] ?? 0;
        
        // If subtotal not provided, calculate from items
        if ($itemTotal == 0) {
            foreach ($orderData['items'] as $item) {
                $itemTotal += $item['price'] * $item['quantity'];
            }
        }
        
        $orderPayload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $orderData['order_id'],
                    'amount' => [
                        'currency_code' => 'PHP',
                        'value' => number_format($orderData['total_amount'], 2, '.', ''),
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'PHP',
                                'value' => number_format($itemTotal, 2, '.', '')
                            ],
                            'shipping' => [
                                'currency_code' => 'PHP',
                                'value' => number_format($shippingCost, 2, '.', '')
                            ],
                            'discount' => [
                                'currency_code' => 'PHP',
                                'value' => number_format($discountAmount, 2, '.', '')
                            ]
                        ]
                    ],
                    'description' => 'FitFuel Order #' . $orderData['order_id'],
                    'items' => $this->formatItems($orderData['items']),
                    'shipping' => $this->formatShippingAddress($orderData['shipping_address'])
                ]
            ],
            'application_context' => [
                'return_url' => $redirectUrls['return_url'],
                'cancel_url' => $redirectUrls['cancel_url'],
                'brand_name' => 'FitFuel',
                'landing_page' => 'BILLING',
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                'user_action' => 'PAY_NOW'
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->credentials['base_url'] . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'PayPal-Request-Id: ' . uniqid()
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201) {
            $data = json_decode($response, true);
            return $data;
        }
        
        throw new Exception('Failed to create PayPal order: ' . $response);
    }
    
    /**
     * Capture PayPal payment
     */
    public function captureOrder($orderId) {
        $accessToken = $this->getAccessToken();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->credentials['base_url'] . '/v2/checkout/orders/' . $orderId . '/capture');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'PayPal-Request-Id: ' . uniqid()
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201) {
            $data = json_decode($response, true);
            return $data;
        }
        
        throw new Exception('Failed to capture PayPal order: ' . $response);
    }
    
    /**
     * Format order items for PayPal
     */
    private function formatItems($items) {
        $formattedItems = [];
        
        foreach ($items as $item) {
            $formattedItems[] = [
                'name' => $item['name'],
                'unit_amount' => [
                    'currency_code' => 'PHP',
                    'value' => number_format($item['price'], 2, '.', '')
                ],
                'quantity' => (string)$item['quantity'],
                'category' => 'PHYSICAL_GOODS'
            ];
        }
        
        return $formattedItems;
    }
    
    /**
     * Format shipping address for PayPal
     */
    private function formatShippingAddress($shippingAddress) {
        // Use PSGC names if available, fallback to legacy columns
        $city = $shippingAddress['city_muni_name'] ?? $shippingAddress['city'] ?? '';
        $state = $shippingAddress['province_name'] ?? $shippingAddress['state'] ?? '';
        $postalCode = $shippingAddress['postal_code'] ?? '';
        
        // Build address lines
        $addressLines = [$shippingAddress['address_line1']];
        if (!empty($shippingAddress['address_line2'])) {
            $addressLines[] = $shippingAddress['address_line2'];
        }
        if (!empty($shippingAddress['address_line3'])) {
            $addressLines[] = $shippingAddress['address_line3'];
        }
        
        return [
            'name' => [
                'full_name' => $shippingAddress['full_name']
            ],
            'address' => [
                'address_line_1' => $addressLines[0],
                'address_line_2' => isset($addressLines[1]) ? $addressLines[1] : '',
                'admin_area_2' => $city, // City
                'admin_area_1' => $state, // State/Province
                'postal_code' => $postalCode,
                'country_code' => 'PH' // Philippines
            ]
        ];
    }
    
    /**
     * Get order details from PayPal
     */
    public function getOrderDetails($orderId) {
        $accessToken = $this->getAccessToken();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->credentials['base_url'] . '/v2/checkout/orders/' . $orderId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        throw new Exception('Failed to get PayPal order details: ' . $response);
    }
}
?>
