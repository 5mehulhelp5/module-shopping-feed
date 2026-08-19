<?php

namespace MageOS\ShoppingFeed\Model\Generator;

class Memory extends \Magento\Framework\DataObject
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    private $usage;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \MageOS\ShoppingFeed\Model\Logger $logger,
        array $data = []
    ) {

        $this->dateTime = $dateTime;
        $this->logger = $logger;
        parent::__construct($data);
    }

    public function setStartUsage()
    {
        $this->usage = $this->getUsage();
    }

    public function getUsage()
    {
        return memory_get_usage(true);
    }

    public function getTotalUsage()
    {
        return $this->getUsage() - $this->usage;
    }

    /**
     * Check if we are using too much memory and the script should stop
     *
     * @return bool
     */
    public function isCloseToPhpLimit($startedAt, $log = true)
    {
        $timeSpent = ($this->dateTime->timestamp() - $startedAt) * 1.1;
        $timeMax = ini_get('max_execution_time');
        if ($timeMax > 0 && $timeSpent >= $timeMax) {
            $this->logger->info('PHP max_execution_time reached.');
            return true;
        }

        $currentUsage = $this->getUsage() * 1.1; // We add 10% overhead so we terminate soon enough
        $maxUsage = $this->getMemoryLimit();
        if ($currentUsage >= $maxUsage) {
            if ($log) {
                $this->logger->info('PHP allowed_memory reached.');
            }
            return true;
        }
        return false;
    }

    /**
     * @param  $totalUsage boolean
     * @return string
     */
    public function format($totalUsage = false)
    {
        $memory = $this->getUsage();
        if ($totalUsage) {
            $memory = $this->getTotalUsage();
        }

        $memory = max(1, $memory);

        $memoryLimit = $this->getMemoryLimit();
        $units = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb'];
        $m = round($memory / pow(1024, ($i = floor(log($memory, 1024)))), 2);
        $limit = round($memoryLimit / pow(1024, ($j = floor(log($memoryLimit, 1024)))), 2);
        return sprintf('%4.2f %s/%4.2f %s', $m, $units[$i], $limit, $units[$j]);
    }

    /**
     * Returns the memory limit in bytes ??
     *
     * @return float
     */
    public function getMemoryLimit()
    {
        if (!$this->hasData('memory_limit')) {
            $memory = ini_get('memory_limit');
            if (is_numeric($memory) && $memory <= 0) {
                return $this->getUsage() * 1.5;
            }

            if (!is_numeric($memory)) {
                preg_match('/^\s*([0-9.]+)\s*([KMGTPE])B?\s*$/i', $memory, $matches);
                $num = (float)$matches[1];
                switch (strtoupper($matches[2])) {
                case 'E':
                    $num = $num * 1024;
                    // move forward
                case 'P':
                    $num = $num * 1024;
                    // move forward
                case 'T':
                    $num = $num * 1024;
                    // move forward
                case 'G':
                    $num = $num * 1024;
                    // move forward
                case 'M':
                    $num = $num * 1024;
                    // move forward
                case 'K':
                    $num = $num * 1024;
                    break;
                }
                $memory = $num;
            }
            $this->setData('memory_limit', $memory);
        }

        return $this->getData('memory_limit');
    }
}
