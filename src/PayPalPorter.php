<?php

   namespace Grayl\Omnipay\PayPal;

   use Grayl\Mixin\Common\Traits\StaticTrait;
   use Grayl\Omnipay\Common\Entity\OmnipayGatewayOffsiteCustomer;
   use Grayl\Omnipay\Common\OmnipayPorterAbstract;
   use Grayl\Omnipay\PayPal\Config\PayPalAPIEndpoint;
   use Grayl\Omnipay\PayPal\Config\PayPalConfig;
   use Grayl\Omnipay\PayPal\Controller\PayPalAuthorizeRequestController;
   use Grayl\Omnipay\PayPal\Controller\PayPalCaptureRequestController;
   use Grayl\Omnipay\PayPal\Controller\PayPalCompleteRequestController;
   use Grayl\Omnipay\PayPal\Controller\PayPalCompleteResponseController;
   use Grayl\Omnipay\PayPal\Entity\PayPalAuthorizeRequestData;
   use Grayl\Omnipay\PayPal\Entity\PayPalCaptureRequestData;
   use Grayl\Omnipay\PayPal\Entity\PayPalCompleteRequestData;
   use Grayl\Omnipay\PayPal\Entity\PayPalGatewayData;
   use Grayl\Omnipay\PayPal\Service\PayPalAuthorizeRequestService;
   use Grayl\Omnipay\PayPal\Service\PayPalAuthorizeResponseService;
   use Grayl\Omnipay\PayPal\Service\PayPalCaptureRequestService;
   use Grayl\Omnipay\PayPal\Service\PayPalCaptureResponseService;
   use Grayl\Omnipay\PayPal\Service\PayPalCompleteRequestService;
   use Grayl\Omnipay\PayPal\Service\PayPalCompleteResponseService;
   use Grayl\Omnipay\PayPal\Service\PayPalGatewayService;
   use Omnipay\Omnipay;
   use Omnipay\PayPal\RestGateway;

   /**
    * Front-end for the PayPal Omnipay package
    * @method PayPalGatewayData getSavedGatewayDataEntity ( string $api_endpoint_id )
    *
    * @package Grayl\Omnipay\PayPal
    */
   class PayPalPorter extends OmnipayPorterAbstract
   {

      // Use the static instance trait
      use StaticTrait;

      /**
       * The name of the config file for the PayPal package
       *
       * @var string
       */
      protected string $config_file = 'omnipay-paypal.php';

      /**
       * The PayPalConfig instance for this gateway
       *
       * @var PayPalConfig
       */
      protected $config;


      /**
       * Creates a new Omnipay ApiGateway object for use in a PayPalGatewayData entity
       *
       * @param PayPalAPIEndpoint $api_endpoint A PayPalAPIEndpoint with credentials needed to create a gateway API object
       *
       * @return RestGateway
       * @throws \Exception
       */
      public function newGatewayAPI ( $api_endpoint ): object
      {

         // Create the Omnipay PayPalGateway api entity
         /* @var $api RestGateway */
         $api = Omnipay::create( 'PayPal_Rest' );

         // Set the environment's credentials into the API
         $api->setClientID( $api_endpoint->getClientID() );
         $api->setSecret( $api_endpoint->getSecret() );

         // Return the new instance
         return $api;
      }


      /**
       * Creates a new PayPalGatewayData entity
       *
       * @param string $api_endpoint_id The API endpoint ID to use (typically "default" if there is only one API gateway)
       *
       * @return PayPalGatewayData
       * @throws \Exception
       */
      public function newGatewayDataEntity ( string $api_endpoint_id ): object
      {

         // Grab the gateway service
         $service = new PayPalGatewayService();

         // Get a new API
         $api = $this->newGatewayAPI( $service->getAPIEndpoint( $this->config,
                                                                $this->environment,
                                                                $api_endpoint_id ) );

         // Configure the API as needed using the service
         $service->configureAPI( $api,
                                 $this->environment );

         // Return the gateway
         return new PayPalGatewayData( $api,
                                       $this->config->getGatewayName(),
                                       $this->environment );
      }


      /**
       * Creates a new PayPalAuthorizeRequestController entity
       * NOTE: You must add items using the ->putItem method after this object is returned
       *
       * @param string $transaction_id The internal transaction ID
       * @param float  $amount         The amount to charge
       * @param string $currency       The base currency of the amount
       *
       * @return PayPalAuthorizeRequestController
       * @throws \Exception
       */
      public function newPayPalAuthorizeRequestController ( string $transaction_id,
                                                            float $amount,
                                                            string $currency ): PayPalAuthorizeRequestController
      {

         // Create the PayPalQueryRequestData entity
         $request_data = new PayPalAuthorizeRequestData( 'authorize',
                                                         $this->getOffsiteURLs() );

         // Set the order parameters
         $request_data->setTransactionID( $transaction_id );
         $request_data->setAmount( $amount );
         $request_data->setCurrency( $currency );

         // Return a new PayPalQueryRequestController entity
         return new PayPalAuthorizeRequestController( $this->getSavedGatewayDataEntity( 'default' ),
                                                      $request_data,
                                                      new PayPalAuthorizeRequestService(),
                                                      new PayPalAuthorizeResponseService() );
      }


      /**
       * Creates a new PayPalCompleteRequestController entity
       *
       * @param string $transaction_id The internal transaction ID
       * @param float  $amount         The amount to charge
       * @param string $currency       The base currency of the amount
       * @param string $reference_id   A reference ID from a PayflowAuthorizeResponseController
       * @param string $payer_id       The payer ID returned from Paypal (a query string parameter found on redirect to complete offsite URL)
       *
       * @return PayPalCompleteRequestController
       * @throws \Exception
       */
      public function newPayPalCompleteRequestController ( string $transaction_id,
                                                           float $amount,
                                                           string $currency,
                                                           string $reference_id,
                                                           string $payer_id ): PayPalCompleteRequestController
      {

         // Create the PayPalQueryRequestData entity
         $request_data = new PayPalCompleteRequestData( 'confirm',
                                                        $this->getOffsiteURLs() );

         // Set the order parameters
         $request_data->setTransactionID( $transaction_id );
         $request_data->setAmount( $amount );
         $request_data->setCurrency( $currency );
         $request_data->setTransactionReference( $reference_id );
         $request_data->setPayerID( $payer_id );

         // Return a new PayPalQueryRequestController entity
         return new PayPalCompleteRequestController( $this->getSavedGatewayDataEntity( 'default' ),
                                                     $request_data,
                                                     new PayPalCompleteRequestService(),
                                                     new PayPalCompleteResponseService() );
      }


      /**
       * Creates a new PayPalCaptureRequestController entity
       *
       * @param string $transaction_id The internal transaction ID
       * @param float  $amount         The amount to charge
       * @param string $currency       The base currency of the amount
       * @param string $reference_id   A reference ID from a PayflowCompleteResponseController
       *
       * @return PayPalCaptureRequestController
       * @throws \Exception
       */
      public function newPayPalCaptureRequestController ( string $transaction_id,
                                                          float $amount,
                                                          string $currency,
                                                          string $reference_id ): PayPalCaptureRequestController
      {

         // Create the PayPalQueryRequestData entity
         $request_data = new PayPalCaptureRequestData( 'capture',
                                                       $this->getOffsiteURLs() );

         // Set the order parameters
         $request_data->setTransactionID( $transaction_id );
         $request_data->setAmount( $amount );
         $request_data->setCurrency( $currency );
         $request_data->setTransactionReference( $reference_id );

         // Return a new PayPalQueryRequestController entity
         return new PayPalCaptureRequestController( $this->getSavedGatewayDataEntity( 'default' ),
                                                    $request_data,
                                                    new PayPalCaptureRequestService(),
                                                    new PayPalCaptureResponseService() );
      }


      /**
       * Creates an OmnipayGatewayOffsiteCustomer from offsite payment data returned in a PayPalCompleteResponseData
       *
       * @param PayPalCompleteResponseController $response The response object to pull the data from
       *
       * @return OmnipayGatewayOffsiteCustomer
       * @throws \Exception
       */
      public function newOmnipayGatewayOffsiteCustomerFromResponse ( $response ): OmnipayGatewayOffsiteCustomer
      {

         // Grab the variables we need
         $data = $response->getData();

         // If we are missing payer data, throw an error
         if ( empty( $data ) || empty( $data[ 'payer' ][ 'payer_info' ][ 'email' ] ) ) {
            // Error, no user data returned
            throw new \Exception( "Offsite customer information missing" );
         }

         // Set the root array of data
         $payer = $data[ 'payer' ][ 'payer_info' ];

         // Determine what address to use
         $address = ( isset( $payer[ 'billing_address' ] ) ) ? $payer[ 'billing_address' ] : $payer[ 'shipping_address' ];

         // Return a new OmnipayGatewayOffsiteCustomer using data from the PayPal response
         return new OmnipayGatewayOffsiteCustomer( $payer[ 'first_name' ],
                                                   $payer[ 'last_name' ],
                                                   $payer[ 'email' ],
                                                   $address[ 'line1' ],
                                                   ( isset( $address[ 'line2' ] ) ) ? $address[ 'line2' ] : null,
                                                   $address[ 'city' ],
                                                   $address[ 'state' ],
                                                   $address[ 'postal_code' ],
                                                   $address[ 'country_code' ],
                                                   null );
      }

   }