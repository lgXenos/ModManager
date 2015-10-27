Установка модуля git
-------------------

Для удачного старта, лучше всего зайти в папка_проекта/.git/config. Удостовериться, что там обязательно есть строчки вида:
~~~
[user]
email = you@email.here
name = RomanSh
~~~ 
Т.к. если они не подхватятся из git global, то проект не запустится.

Следующее: т.к. все скрипты работают от пользователя www-data(зависит от системы), то для операций записи потребуется небольшая модификация системы от имени root.

Есть 2 пути: 

1. Изменить имя пользователя от которого работает Apache на себя
---

~~~
от имени root, видимо:
export  APACHE _RUN_USER=roman
export  APACHE _RUN_GROUP=roman
~~~

2. Или немного поколдовать с правами на каталоги с сайтами
---

Для начала нужно добавить www-data к себе в группу. И наоборот

~~~
~$ whoami
romanSh
~$ sudo addgroup www-data romanSh
~$ sudo addgroup romanSh www-data 
~~~

Сделать его "хозяином" сайтов:
~~~
root# sudo chown www-data /opt/var/www/.* -R
root# sudo chgrp roman /opt/var/www/.* -R
root# sudo chmod -R u=rwx,g=rwx,o=r /opt/var/www/.*
~~~

Далее дать ему свои ssh-ключи.
~~~
~$ sudo cp ~romanSh/.ssh/ ~www-data/ -R
~$ sudo chown www-data ~www-data/.ssh -R
~~~

По идее - все. Надо перезагрузится и можно начинать работать.
---


PS: уже после начала работы, находясь в поисках очередной проблемы, натолкнулся на то, что я решил сделать тот же GitWeb.
Ну да ладно.
:)