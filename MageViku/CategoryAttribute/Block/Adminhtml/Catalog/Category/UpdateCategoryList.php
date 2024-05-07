<?php
namespace MageViku\CategoryAttribute\Block\Adminhtml\Catalog\Category;

use Magento\Backend\Block\Template;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\LocalizedException;

class UpdateCategoryList extends Template
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollection;

    /**
    * @param Template\Context $context
    * @param CategoryCollectionFactory $categoryCollection
    * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CategoryCollectionFactory $categoryCollection,
        array $data = []
    ) {
        $this->categoryCollection = $categoryCollection;
        parent::__construct($context, $data);
    }

    /**
     * Get Categories tree.
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCategoryTree()
    {
        $rootCategoryId = Category::TREE_ROOT_ID; // load categories your root category ID.
        $categories = $this->getChildrenCategories($rootCategoryId);
        return $this->buildTree($categories, $rootCategoryId);
    }

    /**
    * @param $parentId
    * @return array
    * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getChildrenCategories($parentId)
    {
        $collection = $this->categoryCollection->create();
        $collection->addAttributeToSelect('*')
            ->addPathFilter('^' . $parentId . '/');

        $categories = [];
        foreach ($collection as $category) {
            $categories[$category->getId()] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'parent_id' => $category->getParentId(),
                'active' => $category->getIsActive()
            ];
        }
        return $categories;
    }

    /**
    * @param $categories
    * @param $parentId
    * @return array
     */
    protected function buildTree(&$categories, $parentId = 0)
    {
        $tree = [];
        foreach ($categories as $categoryId => &$category) {
            if ($category['parent_id'] == $parentId) {
                $tree[$categoryId] = &$category;
                $tree[$categoryId]['children'] = $this->buildTree($categories, $categoryId);
            }
        }
        return $tree;
    }

    /**
    * @param $categories
    * @return string
     */
    public function renderCategories($categories)
    {
        $html = '';
        foreach ($categories as $category) {
            $categoryActiveCls = $category['active'] ? 'active' : 'no-active';
            $html .= '<li class="x-tree-node">';
            if (!empty($category['children'])) {
                $html .= '<div class="x-tree-node-el folder ' . $categoryActiveCls . '-category" id="expan-category_' . $category['id'] . '">
                    <img src="" class="x-tree-ec-icon x-tree-elbow-plus" id="ext-gen'. $category['id'] .'"/>
                    <input type="checkbox" id="category_' . $category['id'] . '" class="category-checkbox" name="categories[]" value="' . $category['id'] . '">
                    <label for="category_' . $category['id'] . '"><span>' . $category['name'] .' (ID: '. $category['id'] . ')' . '</span></label>
                </div>';
            }else{
                $html .= '<div class="x-tree-node-el folder active-category" id="expan-category_' . $category['id'] . '">
                    <input type="checkbox" id="category_' . $category['id'] . '" class="category-checkbox" name="categories[]" value="' . $category['id'] . '">
                    <label for="category_' . $category['id'] . '"><span>' . $category['name'] .' (ID: '. $category['id'] . ')' . '</span></label>
                </div>';
            }
            if (!empty($category['children'])) {
                $html .= '<ul class="child-categories" style="display: none;">'; // Initially hide child categories
                $html .= $this->renderCategories($category['children']);
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }

    /**
     * Returns action url.
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('categoryattribute/category/action_attribute_edit/', ['_secure' => true]);
    }
}
