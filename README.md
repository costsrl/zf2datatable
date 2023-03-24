# README #
START DEVELOP ZF2DATATABLE ZF2.5

This README would normally document whatever steps are necessary to get your application up and running.

### What is this repository for? ###

* Quick summary
* Version
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo)

### How do I get set up? ###

* Summary of set up
* Configuration
* Dependencies
* Database configuration
* How to run tests
* Deployment instructions

### Contribution guidelines ###

* Writing tests
* Code review
* Other guidelines

### Who do I talk to? ###

* Repo owner or admin
* Other community or team contact


### console usage
* php index.php show grid --sortBys='L_NAME' --sortDirs='DESC' 
* php index.php show grid --sortBys='L_NAME' --sortDirs='DESC' --controller='AdminApplication\Controller\Index'
* php index.php show grid --sortBys='L_NAME' --sortDirs='DESC' --controller='AdminApplication\Controller\Index' --action='language'
 



FUNZIONAMENTO FILTRI

 	> 5  (maggiore di 5)
 	>=5  (maggiore o uguale a 5)
	<=5   (minore o uguale a 5)
	5	  (minore di 5)
	<>5   (diverso da 5)
	1 <> 3 (compreso tra 3 e 5 inclusi between)
	1,2,6 (clausola in)
	!=(3,4,5)(clausola not in)
	Co%  (like a destra)
	%Co  (like a sinistra)
	%Co%  (like destra e Sinistra)


ELEMENTI

DATETIMECALENDAR
 /**
     * @ORM\Column(type="datetime", nullable=true, name="datainizio")
     * @Annotation\Type("Zf2datatable\Form\Element\DateTimeCalendar")
     * @Annotation\Options({"label":"Data Inizio:"})
     * @Annotation\Attributes({"id":"datainizio", "class":"form-control"})
     * @Annotation\Filter({"name":"Zf2datatable\Filter\DateTimeCalendar","options":{"format":"d/m/Y H:i:s"}})
     * @Annotation\Validator({"name":"Zend\Validator\Date","options":{"format":"Y-m-d H:i:s"}})
     * @Annotation\Attributes({"id":"datainizio", "class":"form-control" ,"jsOption":"language:'it',pickTime:true,format: 'DD/MM/YYYY HH:mm:ss'"})
     * @var string
     *
     */

