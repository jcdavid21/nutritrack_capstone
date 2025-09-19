<?php
// Place this file in: ./child_data/meal_plan_generator.php

header('Content-Type: application/json');
include "../../backend/config.php";

if (!isset($_GET['child_id']) || empty($_GET['child_id'])) {
    echo json_encode(['error' => 'Child ID is required']);
    exit;
}

$child_id = intval($_GET['child_id']);

try {
    // Get child details with latest nutrition record - updated table names
    $query = "SELECT 
                c.child_id,
                c.first_name,
                c.last_name,
                c.gender,
                c.birthdate,
                nr.weight,
                nr.height,
                nr.bmi,
                ns.status_name,
                nr.date_recorded
              FROM tbl_child c
              LEFT JOIN tbl_nutrition_record nr ON c.child_id = nr.child_id
              LEFT JOIN tbl_nutrition_status ns ON nr.status_id = ns.status_id
              WHERE c.child_id = ? AND nr.date_recorded = (
                  SELECT MAX(date_recorded) 
                  FROM tbl_nutrition_record 
                  WHERE child_id = c.child_id
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $child_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Child not found or no nutrition records available']);
        exit;
    }
    
    $child = $result->fetch_assoc();
    
    // Calculate age in years
    $birthdate = new DateTime($child['birthdate']);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;
    
    // Generate meal plan based on age, BMI, and nutritional status
    $mealPlan = generateMealPlan($child, $age);
    
    echo json_encode([
        'success' => true,
        'child' => $child,
        'age' => $age,
        'meal_plan' => $mealPlan
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

function generateMealPlan($child, $age) {
    $status = strtolower($child['status_name']);
    $gender = strtolower($child['gender']);
    $bmi = floatval($child['bmi']);
    
    // Determine age group
    $ageGroup = getAgeGroup($age);
    
    // Generate meals based on nutritional status and age
    if (strpos($status, 'underweight') !== false || strpos($status, 'severely') !== false) {
        $mealPlan = getUnderweightMeals($ageGroup, $gender);
        $benefits = getUnderweightBenefits();
        $notes = getUnderweightNotes($age);
        $goals = getUnderweightGoals();
    } elseif (strpos($status, 'overweight') !== false) {
        $mealPlan = getOverweightMeals($ageGroup, $gender);
        $benefits = getOverweightBenefits();
        $notes = getOverweightNotes($age);
        $goals = getOverweightGoals();
    } else {
        $mealPlan = getNormalWeightMeals($ageGroup, $gender);
        $benefits = getNormalWeightBenefits();
        $notes = getNormalWeightNotes($age);
        $goals = getNormalWeightGoals();
    }
    
    return [
        'status_category' => ucfirst(str_replace('_', ' ', getStatusCategory($status))),
        'age_group' => $ageGroup,
        'daily_meals' => $mealPlan,
        'benefits' => $benefits,
        'important_notes' => $notes,
        'estimated_daily_cost' => calculateEstimatedCost($mealPlan),
        'nutritional_goals' => $goals,
        'feeding_tips' => getFeedingTips($ageGroup, $status)
    ];
}

function getAgeGroup($age) {
    if ($age <= 2) return 'toddler';
    if ($age <= 5) return 'preschooler';
    if ($age <= 12) return 'school_age';
    return 'adolescent';
}

function getStatusCategory($status) {
    if (strpos($status, 'underweight') !== false || strpos($status, 'severely') !== false) {
        return 'underweight';
    } elseif (strpos($status, 'overweight') !== false) {
        return 'overweight';
    }
    return 'normal_weight';
}

function getUnderweightMeals($ageGroup, $gender) {
    $meals = [
        'toddler' => [
            'breakfast' => [
                'Rice porridge with evaporated milk',
                '1 slice bread with peanut butter',
                '1/2 cup fresh milk'
            ],
            'morning_snack' => [
                'Banana with peanut butter',
                'Water'
            ],
            'lunch' => [
                '1 cup rice',
                'Sauteed canned sardines with vegetables',
                'Malunggay (moringa) soup',
                'Fresh fruit (banana/apple)'
            ],
            'afternoon_snack' => [
                'Sweet corn with milk (small cup)',
                'Crackers'
            ],
            'dinner' => [
                '1 cup rice',
                'Chicken adobo (1 piece)',
                'Sauteed water spinach',
                'Water'
            ]
        ],
        'preschooler' => [
            'breakfast' => [
                'Rice porridge with egg and vegetables',
                '1 boiled egg',
                'Fresh milk (1 glass)'
            ],
            'morning_snack' => [
                'Peanut butter sandwich',
                'Water or juice'
            ],
            'lunch' => [
                '1.5 cups rice',
                'Pork menudo with potatoes and carrots',
                'Miso soup with tofu',
                'Fresh fruit'
            ],
            'afternoon_snack' => [
                'Fried banana on stick (1 piece)',
                'Fresh milk'
            ],
            'dinner' => [
                '1.5 cups rice',
                'Fried fish with sauce',
                'Sauteed mixed vegetables',
                'Water'
            ]
        ],
        'school_age' => [
            'breakfast' => [
                'Rice with scrambled eggs',
                'Vegetable fried rice',
                'Fresh milk (1 glass)',
                'Fresh fruit'
            ],
            'morning_snack' => [
                'Peanut butter and banana sandwich',
                'Water'
            ],
            'lunch' => [
                '2 cups rice',
                'Chicken curry with vegetables',
                'Mung bean soup',
                'Fresh fruit'
            ],
            'afternoon_snack' => [
                'Spring roll with banana (1-2 pieces)',
                'Fresh juice'
            ],
            'dinner' => [
                '2 cups rice',
                'Pork sisig',
                'Mixed vegetable stew (pinakbet)',
                'Water'
            ]
        ],
        'adolescent' => [
            'breakfast' => [
                'Beef tapa with rice and egg',
                'Fresh milk (1 glass)',
                'Fresh fruit'
            ],
            'morning_snack' => [
                'Sandwich with cheese and ham',
                'Fresh juice'
            ],
            'lunch' => [
                '2-2.5 cups rice',
                'Crispy pork belly with vegetables',
                'Pork sinigang (sour soup)',
                'Fresh fruit'
            ],
            'afternoon_snack' => [
                'Rice noodles with sauce (small serving)',
                'Water'
            ],
            'dinner' => [
                '2-2.5 cups rice',
                'Grilled milkfish',
                'Adobo water spinach',
                'Water'
            ]
        ]
    ];
    
    return $meals[$ageGroup];
}

function getOverweightMeals($ageGroup, $gender) {
    $meals = [
        'toddler' => [
            'breakfast' => [
                'Oatmeal with fresh fruits (no sugar)',
                '1 glass low-fat milk',
                'Water'
            ],
            'morning_snack' => [
                'Fresh fruits (apple slices)',
                'Water'
            ],
            'lunch' => [
                '3/4 cup brown rice',
                'Steamed fish with vegetables',
                'Clear soup (no oil)',
                'Water'
            ],
            'afternoon_snack' => [
                'Vegetable sticks (cucumber, carrots)',
                'Water'
            ],
            'dinner' => [
                '3/4 cup brown rice',
                'Grilled chicken breast (no skin)',
                'Steamed vegetables',
                'Water'
            ]
        ],
        'preschooler' => [
            'breakfast' => [
                'Brown rice with scrambled egg whites',
                'Fresh fruit',
                'Low-fat milk'
            ],
            'morning_snack' => [
                'Fresh fruit salad (no dressing)',
                'Water'
            ],
            'lunch' => [
                '1 cup brown rice',
                'Grilled fish with minimal oil',
                'Clear vegetable soup',
                'Water'
            ],
            'afternoon_snack' => [
                'Steamed sweet potato (small)',
                'Water'
            ],
            'dinner' => [
                '1 cup brown rice',
                'Chicken soup with vegetables (less oil)',
                'Water'
            ]
        ],
        'school_age' => [
            'breakfast' => [
                'Brown rice with boiled egg',
                'Fresh vegetables',
                'Water or unsweetened juice'
            ],
            'morning_snack' => [
                'Fresh fruit',
                'Water'
            ],
            'lunch' => [
                '1-1.5 cups brown rice',
                'Grilled milkfish',
                'Mixed vegetable soup',
                'Water'
            ],
            'afternoon_snack' => [
                'Boiled corn (small)',
                'Water'
            ],
            'dinner' => [
                '1-1.5 cups brown rice',
                'Steamed chicken with vegetables',
                'Water'
            ]
        ],
        'adolescent' => [
            'breakfast' => [
                'Brown rice with egg whites',
                'Fresh fruit',
                'Low-fat milk or water'
            ],
            'morning_snack' => [
                'Mixed fruit salad',
                'Water'
            ],
            'lunch' => [
                '1.5-2 cups brown rice',
                'Grilled lean meat',
                'Vegetable salad',
                'Water'
            ],
            'afternoon_snack' => [
                'Boiled sweet potato',
                'Water'
            ],
            'dinner' => [
                '1.5-2 cups brown rice',
                'Fish soup with vegetables',
                'Water'
            ]
        ]
    ];
    
    return $meals[$ageGroup];
}

function getNormalWeightMeals($ageGroup, $gender) {
    $meals = [
        'toddler' => [
            'breakfast' => [
                'Rice porridge with egg',
                'Fresh milk',
                'Fresh fruit'
            ],
            'morning_snack' => [
                'Fresh fruit',
                'Water'
            ],
            'lunch' => [
                '1 cup rice',
                'Fish with vegetables',
                'Soup',
                'Water'
            ],
            'afternoon_snack' => [
                'Crackers with milk',
                'Water'
            ],
            'dinner' => [
                '1 cup rice',
                'Chicken with vegetables',
                'Water'
            ]
        ],
        'preschooler' => [
            'breakfast' => [
                'Rice with egg',
                'Fresh milk',
                'Fresh fruit'
            ],
            'morning_snack' => [
                'Banana or other fruit',
                'Water'
            ],
            'lunch' => [
                '1.5 cups rice',
                'Chicken adobo',
                'Vegetable soup',
                'Fresh fruit'
            ],
            'afternoon_snack' => [
                'Rice cake (small)',
                'Water'
            ],
            'dinner' => [
                '1.5 cups rice',
                'Fried fish',
                'Sauteed vegetables',
                'Water'
            ]
        ],
        'school_age' => [
            'breakfast' => [
                'Filipino breakfast meals (tapa/longganisa with rice)',
                'Fresh milk',
                'Fresh fruit'
            ],
            'morning_snack' => [
                'Sandwich or crackers',
                'Juice or water'
            ],
            'lunch' => [
                '2 cups rice',
                'Pork/beef dish with vegetables',
                'Soup (sinigang, nilaga)',
                'Fresh fruit'
            ],
            'afternoon_snack' => [
                'Filipino snacks (rice cakes)',
                'Water'
            ],
            'dinner' => [
                '2 cups rice',
                'Fish or chicken',
                'Vegetables',
                'Water'
            ]
        ],
        'adolescent' => [
            'breakfast' => [
                'Complete Filipino breakfast',
                'Milk or coffee',
                'Fresh fruit'
            ],
            'morning_snack' => [
                'Sandwich or pastry',
                'Beverage'
            ],
            'lunch' => [
                '2-2.5 cups rice',
                'Meat/fish with vegetables',
                'Soup',
                'Fresh fruit'
            ],
            'afternoon_snack' => [
                'Filipino afternoon snack',
                'Beverage'
            ],
            'dinner' => [
                '2-2.5 cups rice',
                'Main dish with vegetables',
                'Water'
            ]
        ]
    ];
    
    return $meals[$ageGroup];
}

function getUnderweightBenefits() {
    return [
        'Helps increase weight in a healthy manner',
        'Provides adequate energy for active lifestyle',
        'Promotes healthy muscle development',
        'Supports brain development and cognitive function',
        'Supplies essential vitamins and minerals',
        'Strengthens immune system',
        'Supports proper bone growth and development'
    ];
}

function getOverweightBenefits() {
    return [
        'Promotes gradual and healthy weight loss',
        'Provides balanced nutrition without excess calories',
        'Improves metabolism and digestive health',
        'Provides sustained energy throughout the day',
        'Supports healthy digestion and gut health',
        'Reduces risk of developing diabetes',
        'Supports cardiovascular health'
    ];
}

function getNormalWeightBenefits() {
    return [
        'Maintains healthy weight for optimal growth',
        'Provides balanced nutrition for development',
        'Supports normal growth patterns',
        'Enhances brain development and learning',
        'Maintains good energy levels',
        'Boosts immune system function',
        'Promotes overall physical and mental wellness'
    ];
}

function getUnderweightNotes($age) {
    return [
        'Provide frequent small meals (5-6 times daily)',
        'Include healthy fats like avocado, nuts, and olive oil',
        'Use full-fat milk products when appropriate',
        'Avoid drinking water before meals to prevent early satiety',
        'Monitor weight gain weekly with healthcare provider',
        'Consult doctor if no improvement after 2-3 months',
        'Encourage light physical activity to stimulate appetite',
        $age < 5 ? 'Ensure foods are soft and easy to swallow' : 'Teach how to prepare healthy high-calorie snacks'
    ];
}

function getOverweightNotes($age) {
    return [
        'Limit sugar and processed foods significantly',
        'Increase vegetable and fruit intake',
        'Encourage regular physical activity daily',
        'Use smaller plates for portion control',
        'Limit screen time and sedentary activities',
        'Encourage adequate water intake throughout day',
        'Do not use food as reward or punishment',
        $age < 5 ? 'Involve child in food preparation activities' : 'Teach making healthy food choices independently'
    ];
}

function getNormalWeightNotes($age) {
    return [
        'Maintain balanced diet with variety of foods',
        'Encourage trying different healthy foods',
        'Regular monitoring of growth patterns',
        'Promote active lifestyle and outdoor play',
        'Teach proper eating habits and table manners',
        'Limit junk foods and sugary drinks',
        'Encourage family meals together',
        $age < 5 ? 'Introduce new foods gradually and patiently' : 'Teach basic meal planning and nutrition'
    ];
}

function getUnderweightGoals() {
    return [
        'Achieve gradual weight gain: 0.5-1 kg per month',
        'Improve appetite and increase food intake',
        'Enhance energy levels and reduce fatigue',
        'Maintain normal growth velocity for age',
        'Increase lean muscle mass appropriately',
        'Strengthen immune function and reduce illness'
    ];
}

function getOverweightGoals() {
    return [
        'Achieve gradual weight loss: 0.5 kg per month',
        'Improve BMI percentile for age and height',
        'Develop healthy eating habits and preferences',
        'Increase daily physical activity levels',
        'Improve energy levels and reduce fatigue',
        'Build positive self-esteem and body image'
    ];
}

function getNormalWeightGoals() {
    return [
        'Maintain healthy weight within normal range',
        'Continue consistent growth pattern',
        'Develop and maintain good eating habits',
        'Maintain active and healthy lifestyle',
        'Ensure adequate nutrition intake',
        'Support overall physical and mental wellness'
    ];
}

function getFeedingTips($ageGroup, $status) {
    $tips = [
        'toddler' => [
            'Offer appropriate finger foods for self-feeding',
            'Make meal time enjoyable and stress-free',
            'Encourage self-feeding to develop motor skills',
            'Serve small, manageable portions',
            'Use colorful plates to make meals appealing'
        ],
        'preschooler' => [
            'Involve child in grocery shopping and food selection',
            'Teach basic food groups and their benefits',
            'Establish consistent meal routines and schedules',
            'Encourage trying new foods without pressure',
            'Make cooking activities fun and educational'
        ],
        'school_age' => [
            'Pack nutritious and appealing school lunches',
            'Teach reading food labels and ingredients',
            'Discuss nutrition and healthy eating concepts',
            'Encourage participation in sports and activities',
            'Plan and prepare family meals together'
        ],
        'adolescent' => [
            'Teach meal preparation and cooking skills',
            'Discuss body image and nutrition positively',
            'Encourage making independent healthy choices',
            'Support increasing independence in food decisions',
            'Provide access to healthy food options at home'
        ]
    ];
    
    return $tips[$ageGroup];
}

function calculateEstimatedCost($mealPlan) {
    // Basic cost estimation for Bulacan prices (PHP per day)
    $baseCosts = [
        'rice' => 15,
        'vegetables' => 25,
        'protein' => 40,
        'fruits' => 20,
        'milk' => 15,
        'snacks' => 20
    ];
    
    // Estimate based on meal complexity
    $totalCost = array_sum($baseCosts);
    
    return [
        'min_cost' => $totalCost * 0.8,
        'max_cost' => $totalCost * 1.2,
        'average_cost' => $totalCost,
        'currency' => 'PHP',
        'note' => 'Estimated daily cost in Philippine Peso (Bulacan prices)'
    ];
}
?>