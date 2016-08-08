<?php
/**
 * Category.php.
 *
 */

namespace T411\MainBundle\Entity;

class Category
{
    /**
     * @var int
     */
    private $id = null;

    /**
     * @var null|Category
     */
    private $parent = null;

    /**
     * @var Category[]
     */
    private $childs;

    /**
     * @var string
     */
    private $name;

    /**
     * @param $json_data
     *
     * @return $this
     */
    public function setFromJson($json_data)
    {
        if (!isset($json_data->id)) {
            return null;
        }

        if ($json_data->id == 456) {
            return null;
        }
        $this->setId($json_data->id)
            ->setName($json_data->name);

        if (isset($json_data->cats)) {
            foreach ($json_data->cats as $jsonSubCat) {
                $newCategory = new Category();
                $newCategory->setFromJson($jsonSubCat);
//                    ->setParent($this);
                if ($newCategory->getId() != null) {
                    $this->addChild($newCategory);
                }
            }
        }

        return $this;
    }

    /**
     * Check if sub-category is inside.
     *
     * @param integer $cid Sub category id.
     *
     * @return bool
     */
    public function contains($cid)
    {
        if ($this->getId() == $cid) {
            return true;
        }
        if ($this->childs) {
            foreach ($this->childs as $child) {
                if ($child->contains($cid)) {
                    return true;
                }
            }
        }


        return false;
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
     * @return Category
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return null|\T411\MainBundle\Entity\Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \T411\MainBundle\Entity\Category $parent
     *
     * @return Category
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

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
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param int $k
     *
     * @return \T411\MainBundle\Entity\Category[]
     */
    public function getChilds($k = null)
    {
        if ($k) {
            return $this->childs[$k];
        } else {
            return $this->childs;
        }
    }

    /**
     * @param \T411\MainBundle\Entity\Category[] $childs
     *
     * @return Category
     */
    public function setChilds($childs)
    {
        $this->childs = $childs;

        return $this;
    }

    /**
     * @param \T411\MainBundle\Entity\Category $child
     *
     * @return Category
     */
    public function addChild($child)
    {
        $this->childs[] = $child;

        return $this;
    }
}