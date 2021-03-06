<?php

   namespace Grayl\Omnipay\PayPal\Service;

   use Grayl\Omnipay\Common\Service\OmnipayRequestServiceAbstract;
   use Grayl\Omnipay\PayPal\Entity\PayPalAuthorizeRequestData;
   use Grayl\Omnipay\PayPal\Entity\PayPalAuthorizeResponseData;
   use Grayl\Omnipay\PayPal\Entity\PayPalGatewayData;
   use Omnipay\PayPal\Message\Response;

   /**
    * Class PayPalAuthorizeRequestService
    * The service for working with the PayPal authorize requests
    *
    * @package Grayl\Omnipay\PayPal
    */
   class PayPalAuthorizeRequestService extends OmnipayRequestServiceAbstract
   {

      /**
       * Sends a PayPalAuthorizeRequestData object to the PayPal gateway and returns a response
       *
       * @param PayPalGatewayData          $gateway_data A configured PayPalGatewayData entity to send the request through
       * @param PayPalAuthorizeRequestData $request_data The PayPalAuthorizeRequestData entity to send
       *
       * @return PayPalAuthorizeResponseData
       * @throws \Exception
       */
      public function sendRequestDataEntity ( $gateway_data,
                                              $request_data ): object
      {

         // Use the abstract class function to send the authorize request and return a response
         return $this->sendAuthorizeRequestData( $gateway_data,
                                                 $request_data );
      }


      /**
       * Creates a new PayPalAuthorizeResponseData object to handle data returned from the gateway
       *
       * @param Response $api_response The response entity received from a gateway
       * @param string   $gateway_name The name of the gateway
       * @param string   $action       The action performed in this response (authorize, capture, etc.)
       * @param string[] $metadata     Extra data associated with this response
       *
       * @return PayPalAuthorizeResponseData
       */
      public function newResponseDataEntity ( $api_response,
                                              string $gateway_name,
                                              string $action,
                                              array $metadata ): object
      {

         // Return a new PayPalAuthorizeResponseData entity
         return new PayPalAuthorizeResponseData( $api_response,
                                                 $gateway_name,
                                                 $action,
                                                 $metadata[ 'amount' ] );
      }

   }