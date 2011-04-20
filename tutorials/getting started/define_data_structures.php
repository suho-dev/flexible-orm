<?php
/*! @page intro_step1 Define Data Structures
 *
 * If you are starting a new project (or adding new tables), then you have the
 * luxury of creating your own new data structures.
 *
 * When creating these, ideally you should work to the following standards:
 * - Table names should be lowercase and plural<br>
 * <i>cars, sheep, giant_lizards</i>
 * - Primary keys should be named <b>id</b>
 * - Foreign keys should be named (singular model name)_id<br/>
 * <i>e.g. car_id, sheep_id</i>
 *
 * \note These are not requirements to run the ORM, as you can simply configure it
 * to use other values.
 *
 * \section intro_step1_tables Tutorial Tables
 *
 * For this tutorial we will assume we have the following tables:
 * - A table named <b>cars</b> with primary key id and foreign keys <i>owner_id</i>
 * and <i>brand</i> (link to table car_manufacturers)
 * - A table named <b>owners</b> with primary key id and the fields <i>name</i>
 * and <i>age</i>
 * - A table named <b>car_manufacturers</b> with primary key <i>name</i> and the
 * field <i>country</i>
 *
 * <table>
 *   <tr><th colspan="7">cars</th></tr>
 *   <tr><th>id*</th><th>owner_id</th><th>brand</th>     <th>model</th>   <th>colour</th><th>doors</th><th>age</th></tr>
 *   <tr><td>1</td>  <td>2</td>       <td>Volkswagen</td><td>Golf GTI</td><td>black</td> <td>5</td>    <td>2</td></tr>
 *   <tr><td>2</td>  <td>1</td>       <td>BMW</td>       <td>335i</td>    <td>white</td> <td>4</td>    <td>3</td></tr>
 *   <tr><td colspan="7"><i>...</i></td></tr>
 * </table>
 * <br />
 *
 * <table>
 *   <tr><th colspan="3">owners</th></tr>
 *   <tr><th>id*</th><th>name</th>  <th>age</th></tr>
 *   <tr><td>1</td>  <td>Jarrod</td><td>31</td></tr>
 *   <tr><td>2</td>  <td>Steve</td> <td>35</td></tr>
 *   <tr><td colspan="3"><i>...</i></td></tr>
 * </table>
 * <br />
 * 
 * <table>
 *   <tr><th colspan="2">car_manufacturers</th></tr>
 *   <tr><th>name*</th>     <th>country</th></tr>
 *   <tr><td>Volkswagen</td><td>Germany</td></tr>
 *   <tr><td>BMW</td>       <td>Germany</td></tr>
 *   <tr><td colspan="2"><i>...</i></td></tr>
 * </table>
 * <br />
 *
 * \section intro_step1_nav Getting Started
 *
 * - <b>Step 1: Define Data Structures</b>
 * - <b>Step 2: \ref intro_step2 "Define Model Classes"</b>
 * - <b>Step 3: \ref intro_step3 "Define Foreign Keys"</b>
 * - <b>Step 4: \ref intro_step4 "Access your data!"</b>
 *
 */