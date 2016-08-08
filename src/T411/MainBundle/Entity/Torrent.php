<?php


namespace T411\MainBundle\Entity;

use Bencoder\Bencode;
use Rych\ByteSize\ByteSize;

/**
 * Class Torrent.
 *
 * @package   T411\MainBundle\Entity
 */
class Torrent
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $rewriteName;

    /**
     * @var int
     */
    private $seeders;


    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var string
     */
    private $content;

    /**
     * Construct from json.
     *
     * @param mixed $json_data Scraped datas.
     *
     * @return void
     */
    public function setFromJson($json_data)
    {
        $this->setId($json_data->id)
            ->setName($json_data->name)
            ->setRewriteName(isset($json_data->rewritename)?$json_data->rewritename:'')
            ->setCategoryId(isset($json_data->category)?$json_data->category:0)
            ->setSize(isset($json_data->size)?$json_data->size:0)
            ->setSeeders(isset($json_data->seeders)?$json_data->seeders:0);
    }

    public function toArray()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'categoryId' => $this->categoryId,
            'seeders' =>  $this->seeders,
            'size' => $this->size,
            'sizeHuman' => ByteSize::formatMetric($this->size)
        );
    }

    /**
     * @param $tracker
     *
     * @return Torrent
     */
    public function setTracker($tracker)
    {
        $torrent_array = Bencode::decode($this->content);
        $torrent_array['announce'] = $tracker;
        $this->content = Bencode::encode($torrent_array);

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Torrent
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Torrent
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getSeeders()
    {
        return $this->seeders;
    }

    /**
     * @param int $seeders
     *
     * @return Torrent
     */
    public function setSeeders($seeders)
    {
        $this->seeders = intval($seeders);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     *
     * @return Torrent
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getSizeHuman()
    {
        return ByteSize::formatMetric($this->size);
    }

    /**
     * @param int $size
     *
     * @return Torrent
     */
    public function setSize($size)
    {
        $this->size = intval($size);

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return Torrent
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getRewriteName()
    {
        return $this->rewriteName;
    }

    /**
     * @param string $rewriteName
     *
     * @return Torrent
     */
    public function setRewriteName($rewriteName)
    {
        $this->rewriteName = $rewriteName;

        return $this;
    }
}