Установка модуля git
-------------------

Т.к. все скрипты работают от пользователя www-data(зависит от системы), то для операций записи потребуется небольшая модификация системы от имени root.
Для начала нужно добавить www-data к себе в группу. И наоборот

~~~
~$ whoami
romanSh
~$ sudo addgroup www-data romanSh
~$ sudo addgroup romanSh www-data 
~~~

Далее дать ему свои ssh-ключи.
~~~
~$ sudo cp ~romanSh/.ssh/ ~www-data/ -R
~$ sudo chown www-data ~www-data/.ssh -R
~~~

Зайти в .git папку, файл config. Удостовериться, что там обязательно есть строчки вида:
~~~
[user]
email = you@email.here
name = RomanSh
~~~ 

По идее - все.
---

Хотя, т.к. теперь апач от имени www-data хозяйничать в нашей папке сайта. И может назаписывать туда. И мы не сможем это изменить без рута... В общем я сделал так:
~~~
root# chown roman {site-dir} -R
root# chgrp www-data {site-dir} -R
root# cd {site-dir}; sudo chmod -R u=rwx,g=rwx,o=r *
~~~
Также, обращаю внимание: чет оно иногда подглючивает со словами "insufficient permission for adding an object to repository database .git/objects". И операция выше возвращает работоспособность репы. Позже надо попробовать дать для папки GID и UID.

PS: уже после начала работы, находясь в поисках очередной проблемы, натолкнулся на то, что я решил сделать тот же GitWeb.
Ну да ладно.
:)