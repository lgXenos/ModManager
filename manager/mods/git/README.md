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

По идее - все.
---
