<?php
/**
 * Created by PhpStorm.
 * User: cameronburns
 * Date: 30/01/2016
 * Time: 2:25 PM
 */

namespace VisageFour\Bundle\ToolsBundle\Services;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * Class AWSS3Utility
 * @package VisageFour\Bundle\ToolsBundle\Services
 *
 * Example service definition:
 *
 * I think this was used for uploading to S3 directly from a front-end component.
 */
class AWSS3Utility {

    private $em;
    private $logger;

    private $awsKey;
    private $awsSecret;

    public function __construct (EntityManager $em, LoggerInterface $logger, $awsKey, $awsSecret) {
        $this->em                   = $em;
        $this->logger               = $logger;

        $this->awsKey               = $awsKey;
        $this->awsSecret            = $awsSecret;
    }

    /**
     * Returns an array with information needed to directly upload images to AWS S3 using CORS
     *
     * method based on S3 direct upload blog:
     * https://www.designedbyaturtle.co.uk/2013/direct-upload-to-s3-with-a-little-help-from-jquery/
     * (and it's revised article, linked to in the article)
     *
     * @param $s3Bucket
     * @param $region
     * @param string $acl
     * @return array
     *
     */
    public function getS3Details ($s3Bucket, $region, $acl = 'private') {
        $algorithm = "AWS4-HMAC-SHA256";
        $service = "s3";
        $date = gmdate("Ymd\THis\Z");
        $shortDate = gmdate("Ymd");
        $requestType = "aws4_request";
        $expires = "86400"; // 24 Hours
        $successStatus = "201";
        $url = "//{$s3Bucket}.{$service}-{$region}.amazonaws.com";

        // Step 1: Generate the Scope
        $scope = [
            $this->awsKey,
            $shortDate,
            $region,
            $service,
            $requestType
        ];
        $credentials = implode('/', $scope);

        // Step 2: Making a Base64 Policy
        $policy = [
            'expiration' => gmdate('Y-m-d\TG:i:s\Z', strtotime('+6 hours')),
            'conditions' => [
                ['bucket' => $s3Bucket],
                ['acl' => $acl],
                ['starts-with', '$key', ''],
                ['starts-with', '$Content-Type', ''],
                ['success_action_status' => $successStatus],
                ['x-amz-credential' => $credentials],
                ['x-amz-algorithm' => $algorithm],
                ['x-amz-date' => $date],
                ['x-amz-expires' => $expires],
            ]
        ];
        $base64Policy = base64_encode(json_encode($policy));

        // Step 3: Signing your Request (Making a Signature)
        $dateKey = hash_hmac('sha256', $shortDate, 'AWS4' . $this->awsSecret, true);
        $dateRegionKey = hash_hmac('sha256', $region, $dateKey, true);
        $dateRegionServiceKey = hash_hmac('sha256', $service, $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', $requestType, $dateRegionServiceKey, true);

        $signature = hash_hmac('sha256', $base64Policy, $signingKey);

        // Step 4: Build form inputs
        // This is the data that will get sent with the form to S3
        $inputs = [
            'Content-Type' => '',
            'acl' => $acl,
            'success_action_status' => $successStatus,
            'policy' => $base64Policy,
            'X-amz-credential' => $credentials,
            'X-amz-algorithm' => $algorithm,
            'X-amz-date' => $date,
            'X-amz-expires' => $expires,
            'X-amz-signature' => $signature
        ];

        return compact('url', 'inputs');
    }
}