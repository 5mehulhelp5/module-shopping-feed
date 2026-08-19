<?php

namespace MageOS\ShoppingFeed\Model\Generator;

use Magento\Framework\Model\AbstractModel;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\FeedFactory;
use MageOS\ShoppingFeed\Model\Generator;

/**
 * Class Queue
 *
 * @package MageOS\ShoppingFeed\Model\Generator
 *
 * @method $this   setFeedId(int $feedId)
 * @method int     getFeedId()
 * @method $this   setIsRead(boolean $isRead)
 */
class Queue extends AbstractModel
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Batch
     */
    protected $batch;

    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Factory
     */
    protected $generatorFactory;

    protected $feedFactory;

    /**
     * @var \MageOS\ShoppingFeed\Model\Serializer
     */
    protected $serializer;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \MageOS\ShoppingFeed\Model\Generator\BatchFactory $batchFactory,
        \MageOS\ShoppingFeed\Model\FeedFactory $feedFactory,
        \MageOS\ShoppingFeed\Model\Generator\Factory $generatorFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->batch = $batchFactory->create();
        $this->generatorFactory = $generatorFactory;
        $this->feedFactory = $feedFactory;
        $this->serializer = $serializer;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue');
    }

    /**
     * @return \MageOS\ShoppingFeed\Model\Generator
     */
    public function getGenerator()
    {
        $feed = $this->feedFactory->create()->load($this->getFeedId());
        return $this->generatorFactory->create($feed, $this);
    }

    public function setRunning()
    {
        if ($this->getId()) {
            $this->setIsRead(true);
            $this->save();
        }
        return $this;
    }

    /**
     * @param  \MageOS\ShoppingFeed\Model\Feed|int $feed
     * @param  \MageOS\ShoppingFeed\Model\
     * @return $this
     */
    public function add($feed, $schedule = null)
    {
        if ($feed instanceof \MageOS\ShoppingFeed\Model\Feed) {
            $feed = $feed->getId();
        }

        if ($this->getId()) {
            $this->setId(null);
        }

        $data = [
            'feed_id'       => $feed,
            'is_read'       => 0
        ];
        if (!is_null($schedule) && !is_null($schedule->getId())) {
            $data['schedule_id'] = $schedule->getId();
        }

        $this->addData($data);
        $this->save();

        return $this;
    }

    protected function _afterLoad()
    {
        $message = $this->getMessage();
        if (!empty($message) && !is_array($message)) {
            $message = $this->serializer->unserialize($message);
            $this->setMessage($message);
        }

        if (is_array($message)) {
            $this->batch->addData($message);
        }

        return parent::_afterLoad();
    }

    public function beforeSave()
    {
        $message = $this->getMessage();
        $batch = $this->batch->getData();
        if (is_array($batch)) {
            $message = $batch;
        }

        if (is_array($message)) {
            $message = $this->serializer->serialize($message);
            $this->setMessage($message);
        }

        return parent::beforeSave();
    }

    /**
     * @return Batch
     */
    public function getBatch()
    {
        return $this->batch;
    }

    /**
     * @param  Batch $batch
     * @return $this
     */
    public function setBatch(Batch $batch)
    {
        $this->batch = $batch;
        return $this;
    }
}
