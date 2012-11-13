phing-datetime
==============

DateTimeTask is a Phing extension which aims to provide the same functionality as tstamptask and to extend it with datetime->add and datetime-sub operations.

'''Example''':

{{{xml

<!-- remove tar-files older then DateInterval (7 days) -->
<datetime>
    <interval property="expire_date" pattern="Y-m-d" operation="sub" interval="P7D" locale="nl_NL"/>
</datetime>

<delete verbose="true">
    <fileset dir="foo" includes="**/*.tar">
        <date datetime="${expire_date}" when="before"/>
    </fileset>
</delete>

}}}