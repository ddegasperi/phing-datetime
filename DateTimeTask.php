<?php
/*
 */
 
require_once 'phing/Task.php';

/**
 * Sets properties to the current time, or offsets from the current time.
 * The default properties are TSTAMP, DSTAMP and TODAY;
 *
 * Based on Phing's Tstamp task.
 * 
 * @author   Daniel Degasperi <mrstackoverflow@hotmail.com>
 * @package  ddegasperi.tasks.datetime
 */
class DateTimeTask extends Task
{
    private $customFormats = array();
    private $customIntervals = array();
    
    /**
     * Adds a custom format
     *
     * @param DateTimeCustomFormat custom format
     */
    public function addFormat(DateTimeCustomFormat $cf)
    {
        $this->customFormats[] = $cf;
    }
    
    /**
     * Adds a custom format
     *
     * @param DateTimeCustomInterval custom format
     */
    public function addInterval(DateTimeCustomInterval $ci)
    {
        $this->customIntervals[] = $ci;
    }

    /**
     * Create the timestamps. Custom ones are done before
     * the standard ones.
     *
     * @throws BuildException
     */
    public function main()
    {
        
        foreach ($this->customFormats as $cf)
        {
            $cf->execute($this);
        }
        
        foreach ($this->customIntervals as $ci)
        {
            $ci->execute($this);
        }
        
        $datetime = new Datetime();
        
        $this->prefixProperty('DSTAMP', $datetime->format('Ymd'));
        
        $this->prefixProperty('TSTAMP', $datetime->format('Hi'));
        
        $this->prefixProperty('TODAY', $datetime->format('F d Y'));
    }
    
    /**
     * helper that encapsulates prefix logic and property setting
     * policy (i.e. we use setNewProperty instead of setProperty).
     */
    public function prefixProperty($name, $value)
    {
        $this->getProject()->setNewProperty($this->prefix . $name, $value);
    }
}

/**
 * ddegasperi.tasks.datetime
 */
class DateTimeCustomFormat
{
    protected $propertyName = "";
    protected $pattern = "";
    protected $locale = "";
    protected $dt;


    public function __construct() {
        $this->dt = new Datetime();
    }
    
    /**
     * The property to receive the datetime string in the given pattern
     *
     * @param propertyName the name of the property.
     */
    public function setProperty($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * The datetime pattern to be used. The values are as
     * defined by the PHP date() function.
     *
     * @param pattern
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }
    
    /**
     * The locale used to create date/time string.
     *
     * @param locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
    
    /**
     * validate parameter and execute the format.
     *
     * @param DateTimeTask reference to task
     */
    public function execute(DateTimeTask $datetime)
    {
        if (empty($this->propertyName))
        {
            throw new BuildException("property attribute must be provided");
        }

        if (empty($this->pattern))
        {
            throw new BuildException("pattern attribute must be provided");
        }
        
        if (!empty($this->locale))
        {
            setlocale(LC_ALL, $this->locale);
        }
        
        $datetime->prefixProperty($this->propertyName, $this->dt->format($this->pattern));
        
        if (!empty($this->locale))
        {
            // reset locale
            setlocale(LC_ALL, NULL);
        }
    }
}

/**
 * @package  ddegasperi.tasks.datetime
 */
class DateTimeCustomInterval extends DateTimeCustomFormat
{
    
    private $interval = "";
    private $operation = "";
    
    /**
     * The dateinterval pattern to be used. The values are as
     * defined by the PHP DateInterval class.
     *
     * @param pattern
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }
    
    /**
     * The datetime operation to be used. The only possible values are add and sub.
     *
     * @param operation
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
    }


    /**
     * validate parameter and execute the format.
     *
     * @param DateTimeTask reference to task
     */
    public function execute(DateTimeTask $datetime)
    {
        
        if (empty($this->interval))
        {
            throw new BuildException("interval attribute must be provided");
        }
        
        $di = new DateInterval($this->interval);
        switch($this->operation)
        {
            case 'add':
                $this->dt->add($di);
                break;
            case 'sub':
                $this->dt->sub($di);
                break;
            default:
                throw new BuildException("operation attribute must be add or sub");
        }
        
        parent::execute($datetime);
    }
}

