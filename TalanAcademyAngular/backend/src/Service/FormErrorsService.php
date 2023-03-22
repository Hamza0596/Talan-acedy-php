<?php
/**
 * Created by PhpStorm.
 * User: wzouaoui
 * Date: 01/04/2019
 * Time: 15:37
 */

namespace App\Service;

use Symfony\Component\Form\FormInterface;

class FormErrorsService
{
    /**
     * Return array of form errors
     *
     * @param FormInterface $form
     * @return array
     */
    public function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface && $childErrors = $this->getErrorsFromForm($childForm)) {
                $errors[$childForm->getName()] = $childErrors;

            }
        }
        return $errors;
    }
}