* customBundle

1. twig 重写
	* 需要复写的代码至于 CustomBundle/Resources/views/目录下，比如要重写 default/index.html.twig
		则 CustomBundle/Resources/views/default/index.html.twig 即可

2. service/Dao 重写
	* 在CustomBunle.php 中重新
		
		```
		public function getRewriteServiceAlias()
	    {
	        return array(
	            'Course:CourseService'
	        );
	    }

	    public function getRewriteDaoAlias()
	    {
	        return array(
	            'Course:CourseDao'
	        );
	    }
    	```

3. dataTag 
	* 放在目录 src/CustomBundle/Extensions/DataTag

4. controller 重写
	* http://symfony.com/doc/current/bundles/inheritance.html