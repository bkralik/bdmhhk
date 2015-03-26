<?php

namespace App\Presenters;

use Nette,
	App\Model,
    Nette\Utils\Finder,
    Nette\Utils\Image;


/**
 * Dokumenty presenter.
 */
class FotogaleriePresenter extends BasePresenter
{

	public function renderDefault()
	{
        $fotky = array();
        
        foreach (Finder::findFiles('*.png')->exclude('*small*')->in("images") as $key => $file) {
            $rozbor = explode(".", $key);
            $miniatura = $rozbor[0]."_small.".$rozbor[1];
            if(!file_exists($miniatura)) {
                $image = Image::fromFile($key);
                $image->resize(300, 300);
                $image->save($miniatura);
            }
            $fotky[] = array("velka" => $key, "miniatura" => $miniatura);
        }  
        
        $this->template->fotky = $fotky;
	}

}
