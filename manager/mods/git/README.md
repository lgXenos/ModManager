Установка модуля git
-------------------

Т.к. все скрипты работают от пользователя www-data(зависит от системы), то для операций записи потребуется небольшая модификация системы от имени root.
Для начала нужно добавить www-data к себе в группу.

~~~
~$ whoami
romanSh
~$ sudo addgroup www-data romanSh
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
~~~

PS: нашел сходную идею http://www.ekzorchik.ru/wordpress/2014/11/install-git-server-and-web-based-interface-to-it/