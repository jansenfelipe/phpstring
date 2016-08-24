<?php

namespace JansenFelipe\PHPString\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CreateClassCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('create-class')
            ->setDescription('Create class with annotations')
            ->addOption(
                'output',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path output class'
            )
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_OPTIONAL,
                'Namespace class'
            );
            /*->addArgument(
                'classname',
                InputArgument::REQUIRED,
                'Class name'
            );*/
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        //$classname = $input->getArgument('classname');

        $dirpath = getcwd() . DIRECTORY_SEPARATOR . ($input->hasOption('output') ? $input->getOption('output') : '.');
        $namespace = $input->getOption('namespace');

        $question = new Question('Class name? ', false);
        $classname = $helper->ask($input, $output, $question);

        $params = array();

        $finish = false;
        $i = 1;

        while (!$finish)
        {
            $decimals = null;
            $dateformat = null;

            $questionName = new Question('Attribute ['.$i.'] name? [Default: Attr'.$i.'] ', 'Attr'.$i);
            $questionSize = new Question('Attribute ['.$i.'] size? [Default: 1] ', 1);
            $questionType = new ChoiceQuestion('Attribute ['.$i.'] type? [Default: text] ', array('Text', 'Numeric', 'Date'), 'Text');

            $name = $helper->ask($input, $output, $questionName);
            $size = $helper->ask($input, $output, $questionSize);
            $type = $helper->ask($input, $output, $questionType);

            if($type == 'Numeric')
            {
                $questionDecimals = new Question('Attribute ['.$i.'] decimals? [Default: null] ', null);
                $decimals = $helper->ask($input, $output, $questionDecimals);
            }

            if($type == 'Date')
            {
                $questionDateformat = new Question('Attribute ['.$i.'] format date? [Default: Ymd] ', 'Ymd');
                $dateformat = $helper->ask($input, $output, $questionDateformat);
            }

            $params[$i] = array(
                'name' => $name,
                'size' => $size,
                'type' => array(
                    'name' => $type,
                    'decimals' => $decimals,
                    'dateformat' => $dateformat
                )
            );

            $questionNext = new ConfirmationQuestion('Next attribute? [Y,n]', true);
            $finish = !$helper->ask($input, $output, $questionNext);

            $i++;
        }

        $content = <<<EOD
<?php

namespace SerasaConsulta\Layouts;

use JansenFelipe\PHPString\Annotations\Text;
use JansenFelipe\PHPString\Annotations\Date;
use JansenFelipe\PHPString\Annotations\Numeric;

class $classname
{
EOD;

        foreach ($params as $key => $param)
        {
            $size = $param['size'];
            $name = $param['name'];
            $typeName = $param['type']['name'];
            $decimals = is_null($param['type']['decimals']) ? '' : ', decimals='.$param['type']['decimals'];
            $dateformat = is_null($param['type']['dateformat']) ? '' : ', format="'.$param['type']['dateformat'].'"';

            $content .= <<<EOD
            
    /**
     * @$typeName(sequence=$key, size=$size$decimals$dateformat)
     */
    public \$$name;

EOD;
        }

        $content .= <<<EOD
}
EOD;


        file_put_contents($dirpath.DIRECTORY_SEPARATOR.$classname.'.php', $content);
    }
}