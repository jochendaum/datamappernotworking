<?php
/**
 * Created by PhpStorm.
 * User: jochen
 * Date: 18/12/18
 * Time: 12:15 PM
 */
namespace App\Form;

use App\Entity\DataSet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class DataSetType extends AbstractType implements DataMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Type', EntityType::class, [
                'class' => \App\Entity\DataSetType::class,
                'choice_label' => 'name'

            ])
            ->add('period_start_daily', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Day',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker'],
                'mapped' => false,
                'required' => false
            ])
            ->add('period_start_weekly', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Week',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker'],
                'mapped' => false,
                'required' => false
            ])
            ->add('period_start_monthly', ChoiceType::class, [
                'required' => true,
                'label' => 'Month',
                'choice_loader' => new CallbackChoiceLoader(function () {
                    $months = [];
                    for ($i = 0; $i <= 12; $i++) {
                        $month = strtotime('first day of this month + ' . $i . ' months');
                        $months[date('M Y', $month)] = date('Y-m-d', $month);
                    }
                    return $months;
                }),
                'mapped' => false,
                'required' => false
            ])
            ->add('upload', CheckboxType::class, [
                'label' => 'Make active for uploads',
                'required' => false
            ])
            ->add( 'active', CheckboxType::class,[
                'label' => 'Active for Downloads',
                'required' => false
            ] )
            ->add('save', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                    'id' => 'btn-user-form'
                ],
                'label' => 'Save'
            ]);

        $builder
            ->setDataMapper($this);
    }



    public function mapDataToForms($data, $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $data) {
            return;
        }
        $forms = iterator_to_array($forms);
        $forms['Type']->setData($data->getType());
        if ($data->getType()) {
            switch ($data->getType()->getId()) {
                case 1:
                    $forms['period_start_daily']->setData($data->getPeriodStart());
                    break;
                case 2:
                    $forms['period_start_weekly']->setData($data->getPeriodStart());
                    break;
                case 3:
                    $forms['period_start_monthly']->setData($data->getPeriodStart()->format('Y-m-d'));
                    break;
            }
        }
        $forms['upload']->setData($data->getUpload());
        $forms['active']->setData($data->getActive());

    }

    public function mapFormsToData($forms, &$data)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        switch (intval($forms['Type']->getViewData())){
            case 1:
                $start = $forms['period_start_daily']->getNormData();
                $end = clone $start;

                break;
            case 2:
                $start = $forms['period_start_weekly']->getNormData();
                $end = clone $start;
                $end->add(new \DateInterval('P6D'));
                break;
            case 3:
                $start = new \DateTime($forms['period_start_monthly']->getNormData());
                $end = clone $start;
                $end->modify(' last day of this month');

                break;
        }
        $data = (new DataSet())
            ->setPeriodStart($start)
            ->setPeriodEnd($end)
            ->setType($forms['Type']->getNormData())
            ->setUpload($forms['upload']->getNormData())
            ->setActive($forms['active']->getNormData())
        ;
        return $data;

    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults( array(
                                    'data_class' => 'App\Entity\DataSet'
                                ) );

    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'dataset';
    }
}